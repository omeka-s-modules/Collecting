<?php
namespace Collecting\Service\ViewHelper;

use Collecting\View\Helper\CollectingPrepareForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingPrepareFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $helpers)
    {
        $services = $helpers->getServiceLocator();
        return new CollectingPrepareForm($services->get('Collecting\MediaTypeManager'));
    }
}
