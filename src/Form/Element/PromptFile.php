<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\File;

class PromptFile extends File
{
    use PromptIsRequiredTrait;

    public function getInputSpecification()
    {
        $spec = parent::getInputSpecification();
        if (!$this->required) {
            // @todo The FileInput input filter adds the UploadFile validator by
            // default, so this element is "required" by default. I need to find
            // a way for this element to be "optional" by avoiding UploadFile's
            // "File was not uploaded" error.
        }
        return $spec;
    }
}
