<?php
namespace Collecting\Controller;

use Collecting\Form\CollectingForm;
use Omeka\Mvc\Exception;
use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $site = $this->api()->read('sites', [
            'slug' => $this->params('site-slug'),
        ])->getContent();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        $this->layout()->setVariable('site', $site);
        return $view;
    }

    public function editAction()
    {
        $site = $this->api()->read('sites', [
            'slug' => $this->params('site-slug'),
        ])->getContent();

        $form = $this->getForm(CollectingForm::class);

        $view = new ViewModel;
        $this->layout()->setVariable('site', $site);
        $view->setVariable('site', $site);
        $view->setVariable('form', $form);
        return $view;
    }

    public function addPromptAction()
    {
        $request = $this->getRequest();
        if (!$request->isXmlHttpRequest()) {
            throw new Exception\NotFoundException;
        }

        $view = new ViewModel;
        $view->setTerminal(true);
        return $view;
    }

    public function editPromptAction()
    {}
}
