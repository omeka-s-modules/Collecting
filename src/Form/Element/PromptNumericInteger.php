<?php
namespace Collecting\Form\Element;

use NumericDataTypes\Form\Element\Integer as IntegerElement;
use Zend\InputFilter\InputProviderInterface;

class PromptNumericInteger extends IntegerElement implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];

    }
}
