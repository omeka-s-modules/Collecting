<?php
namespace Collecting\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;
use Zend\Form\ElementInterface;

class FormRecaptcha extends AbstractHelper
{
    public function __invoke(ElementInterface $element)
    {
        return $this->render($element);
    }

    public function render(ElementInterface $element)
    {
        $this->getView()->headScript()
            ->appendFile('https://www.google.com/recaptcha/api.js');
        return sprintf(
            '<div %s></div>',
            $this->createAttributesString($element->getAttributes())
        );
    }
}
