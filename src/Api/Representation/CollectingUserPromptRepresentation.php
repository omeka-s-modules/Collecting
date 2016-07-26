<?php
namespace Collecting\Api\Representation;

use Collecting\Entity\CollectingUserPrompt;
use Omeka\Api\Representation\AbstractRepresentation;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingUserPromptRepresentation extends AbstractRepresentation
{
    public function __construct(CollectingUserPrompt $resource, ServiceLocatorInterface $serviceLocator)
    {
        $this->resource = $resource;
        $this->setServiceLocator($serviceLocator);
    }

    public function jsonSerialize()
    {
        return [
            'o:id' => $this->id(),
            'o-module-collecting:text' => $this->text(),
            'o-module-collecting:input_type' => $this->inputType(),
            'o-module-collecting:select_options' => $this->selectOptions(),
            'o-module-collecting:required' => $this->required(),
        ];
    }

    public function id()
    {
        return $this->resource->getId();
    }

    public function text()
    {
        return $this->resource->getText();
    }

    public function inputType()
    {
        return $this->resource->getInputType();
    }

    public function selectOptions()
    {
        return $this->resource->getSelectOptions();
    }

    public function required()
    {
        return $this->resource->getRequired();
    }
}
