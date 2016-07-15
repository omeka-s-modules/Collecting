<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Textarea;
use Zend\InputFilter\InputProviderInterface;

class PromptTextarea extends Textarea implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];

    }
}
