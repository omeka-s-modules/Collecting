<?php
namespace Collecting\Form;

use Zend\Form\Form;

class CollectingPromptForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o-module-collecting:type',
            'type' => 'Select',
            'options' => [
                'label' => 'Type', // @translate
                'value_options' => [
                    'property' => 'Property (Item)', // @translate
                    'media' => 'Media', // @translate
                    'supplementary' => 'Supplementary', // @translate
                ],
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'name' => 'o-module-collecting:text',
            'type' => 'Textarea',
            'options' => [
                'label' => 'Text', // @translate
            ],
            'attributes' => [
                'required' => false,
            ],
        ]);
    }
}
