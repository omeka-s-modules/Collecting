<?php
namespace Collecting\Form\Element;

use Omeka\Form\Element\ResourceSelect;

class PromptItem extends ResourceSelect
{
    use PromptIsMultipleTrait;
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();
        $spec['required'] = $this->required;
        return $spec;
    }
}
