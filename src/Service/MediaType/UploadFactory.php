<?php
namespace Collecting\Service\MediaType;

use Collecting\MediaType\Upload;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UploadFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $mediaTypes)
    {
        $plugins = $mediaTypes->getServiceLocator()->get('ControllerPluginManager');
        return new Upload($plugins);
    }
}
