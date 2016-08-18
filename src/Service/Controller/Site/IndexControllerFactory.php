<?php
namespace Collecting\Service\Controller\Site;

use Collecting\Controller\Site\IndexController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IndexControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        return new IndexController(
            $services->get('Omeka\Acl'),
            $services->get('Collecting\MediaTypeManager')
        );
    }
}
