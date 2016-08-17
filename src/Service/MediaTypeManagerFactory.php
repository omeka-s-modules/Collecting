<?php
namespace Collecting\Service;

use Collecting\MediaType\Manager;
use Omeka\Service\Exception;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MediaTypeManagerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('Config');
        if (!isset($config['media_ingesters'])) {
            throw new Exception\ConfigException('Missing collecting media type configuration');
        }
        return new Manager($serviceLocator, $config['collecting_media_types']);
    }
}
