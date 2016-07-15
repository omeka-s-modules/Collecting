<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Url;

class PromptUrl extends Url
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();
        $spec['required'] = $this->required;
        return $spec;
    }
}
