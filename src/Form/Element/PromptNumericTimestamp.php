<?php
namespace Collecting\Form\Element;

use NumericDataTypes\Form\Element\Timestamp as TimestampElement;
use Zend\InputFilter\InputProviderInterface;

class PromptNumericTimestamp extends TimestampElement implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];

    }
}
