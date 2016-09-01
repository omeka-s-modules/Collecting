<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Email;
use Zend\InputFilter\InputProviderInterface;

class PromptEmail extends Email implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];
    }
}
