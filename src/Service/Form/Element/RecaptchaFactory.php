<?php
namespace Collecting\Service\Form\Element;

use Collecting\Form\Element\Recaptcha;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RecaptchaFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $formElements)
    {
        $services = $formElements->getServiceLocator();

        // Map the element to the view helper that renders it.
        $services->get('ViewHelperManager')->get('FormElement')
            ->addType('recaptcha', 'formRecaptcha');

        return (new Recaptcha)->setClient($services->get('Omeka\HttpClient'));
    }
}
