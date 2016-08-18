<?php
namespace Collecting\MediaType;

use Omeka\Api\Exception;
use Omeka\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\ServiceNotFoundException;

class Manager extends AbstractPluginManager
{
    protected $canonicalNamesReplacements = [];

    public function get($name, $options = [],
        $usePeeringServiceManagers = true
    ) {
        try {
            $instance = parent::get($name, $options, $usePeeringServiceManagers);
        } catch (ServiceNotFoundException $e) {
            $instance = new Fallback($name, $this->getServiceLocator()->get('MvcTranslator'));
        }
        return $instance;
    }

    public function validatePlugin($plugin)
    {
        if (!is_subclass_of($plugin, 'Collecting\MediaType\MediaTypeInterface')) {
            throw new Exception\InvalidAdapterException(sprintf(
                'The collecting media type class "%1$s" does not implement Collecting\MediaType\MediaTypeInterface.',
                get_class($plugin)
            ));
        }
    }
}
