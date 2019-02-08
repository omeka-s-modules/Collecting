<?php
namespace Collecting\Form\Element;

use NumericDataTypes\Form\Element\Interval as IntervalElement;
use Zend\InputFilter\InputProviderInterface;

class PromptNumericInterval extends IntervalElement implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];

    }
}
