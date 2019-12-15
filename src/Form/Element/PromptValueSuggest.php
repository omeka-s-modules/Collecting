<?php
namespace Collecting\Form\Element;

use Zend\Form\Element\Text;
use Zend\InputFilter\InputProviderInterface;

class PromptValueSuggest extends Text implements InputProviderInterface
{
    use PromptIsRequiredTrait;

    /**
     * @var string
     */
    protected $dataType;

    protected $attributes = [
        'type' => 'text',
        'class' => 'valuesuggest-input',
        'data-uri' => '',
        'data-value' => '',
    ];

    public function getInputSpecification()
    {
        return [
            'required' => $this->required,
        ];
    }

    /**
     * Set the data type.
     *
     * @param string $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        $this->setAttribute('data-data-type', $this->dataType);
        return $this;
    }

    /**
     * Get the data type.
     *
     * @return string
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    // TODO Add a validator for link (absolute uri + text, but sometime only value).
}
