<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Text;
use Zend\InputFilter\InputProviderInterface;

class PromptText extends Text implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];

    }
}
