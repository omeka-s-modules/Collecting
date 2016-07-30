<?php
namespace Collecting\Controller\SiteAdmin;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

class ItemController extends AbstractActionController
{
    public function indexAction()
    {
        $site = $this->currentSite();
        $cForm = $this->collectingCurrentForm();
        $cItems = $this->api()
            ->search('collecting_items', ['form_id' => $cForm->id()])->getContent();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        $view->setVariable('cForm', $cForm);
        $view->setVariable('cItems', $cItems);
        return $view;
    }

    public function showAction()
    {
        $site = $this->currentSite();
        $cForm = $this->collectingCurrentForm();
        $cItem = $this->api()
            ->read('collecting_items', $this->params('item-id'))->getContent();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        $view->setVariable('cForm', $cForm);
        $view->setVariable('cItem', $cItem);
        return $view;
    }
}