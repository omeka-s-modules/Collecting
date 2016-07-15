<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Select;

class PromptSelect extends Select
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();
        $spec['required'] = $this->required;
        return $spec;
    }
}
