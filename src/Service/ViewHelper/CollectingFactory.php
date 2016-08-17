<?php
namespace Collecting\Service\ViewHelper;

use Collecting\View\Helper\Collecting;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $helpers)
    {
        $services = $helpers->getServiceLocator();
        return new Collecting($services->get('Collecting\MediaTypeManager'));
    }
}
