<?php
namespace Collecting\Service\Form\Element;

use Collecting\Form\Element\Recaptcha;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RecaptchaFactory implements FactoryInterface
{
    protected $options = [];

    public function createService(ServiceLocatorInterface $formElements)
    {
        $services = $formElements->getServiceLocator();

        // Map the element to the view helper that renders it.
        $services->get('ViewHelperManager')->get('FormElement')
            ->addType('recaptcha', 'formRecaptcha');

        $element = new Recaptcha(null, $this->options);
        $element->setClient($services->get('Omeka\HttpClient'));
        return $element;
    }

    public function setCreationOptions(array $options)
    {
        $this->options = $options;
    }
}
