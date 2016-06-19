<?php
namespace Collecting\Form;

use Zend\Form\Form;

class CollectingForm extends Form
{
    public function init()
    {
        $this->add([
            'name' => 'o-module-collecting:label',
            'type' => 'Text',
            'options' => [
                'label' => 'Label', // @translate
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'name' => 'o-module-collecting:description',
            'type' => 'Textarea',
            'options' => [
                'label' => 'Description', // @translate
            ],
            'attributes' => [
                'required' => false,
            ],
        ]);
    }
}
