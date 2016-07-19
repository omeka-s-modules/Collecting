<?php
namespace Collecting\Service\Form\Element;

use Collecting\Form\Element\Recaptcha;
use Collecting\Validator\Recaptcha as RecaptchaValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RecaptchaFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $formElements)
    {
        $services = $formElements->getServiceLocator();

        // Prepare the element renderer.
        $services->get('ViewHelperManager')->get('FormElement')
            ->addType('recaptcha', 'formRecaptcha');

        // Prepare the validator.
        $validator = new RecaptchaValidator;
        $validator->setClient($services->get('Omeka\HttpClient'));

        $element = new Recaptcha;
        $element->setValidator($validator);
        return $element;
    }
}
