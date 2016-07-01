<?php
namespace Collecting\Controller\Admin;

use Collecting\Form\CollectingForm;
use Omeka\Form\ConfirmForm;
use Omeka\Mvc\Exception;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function adminAction()
    {
        $site = $this->currentSite();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        return $view;
    }

    public function indexAction()
    {
        $site = $this->currentSite();
        $collectingForms = $this->api()
            ->search('collecting_forms', ['site_id' => $site->id()])->getContent();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        $view->setVariable('collectingForms', $collectingForms);
        return $view;
    }

    public function addAction()
    {
        return $this->handleAddEdit();
    }

    public function editAction()
    {
        return $this->handleAddEdit();
    }

    protected function handleAddEdit()
    {
        $site = $this->currentSite();
        $form = $this->getForm(CollectingForm::class);
        $isEdit = (bool) ('edit' === $this->params('action'));

        $view = new ViewModel;
        $view->setTemplate('collecting/admin/index/form');
        $view->setVariable('site', $site);
        $view->setVariable('form', $form);
        $view->setVariable('isEdit', $isEdit);

        if ($isEdit) {
            $collectingForm = $this->api()
                ->read('collecting_forms', $this->params('id'))->getContent();
            $form->setData($collectingForm->jsonSerialize());
            $view->setVariable('collectingForm', $collectingForm);
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $form->setData($data);
            if ($form->isValid()) {
                $response = $isEdit
                    ? $this->api()->update('collecting_forms', $collectingForm->id(), $data)
                    : $this->api()->create('collecting_forms', $data);
                if ($response->isError()) {
                    $form->setMessages($response->getErrors());
                } else {
                    $successMessage = $isEdit
                        ? $this->translate('Successfully updated the collecting form.')
                        : $this->translate('Successfully added the collecting form.');
                    $this->messenger()->addSuccess($successMessage);
                    return $this->redirect()->toUrl($collectingForm->url('show'));
                }
            } else {
                $this->messenger()->addError($this->translate('There was an error during validation.'));
            }
        }

        return $view;
    }

    public function deleteConfirmAction()
    {
        $site = $this->currentSite();
        $collectingForm = $this->api()
            ->read('collecting_forms', $this->params('id'))->getContent();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resourceLabel', 'collecting form');
        $view->setVariable('resource', $collectingForm);
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api()->delete('collecting_forms', $this->params('id'));
                if ($response->isError()) {
                    $this->messenger()->addError($this->translate('The collecting form could not be deleted.'));
                } else {
                    $this->messenger()->addSuccess($this->translate('Successfully deleted the collecting form.'));
                }
            } else {
                $this->messenger()->addError($this->translate('There was an error during validation.'));
            }
        }
        return $this->redirect()->toRoute(
            'admin/site/slug/collecting/default',
            ['action' => 'index'],
            true
        );
    }
}

