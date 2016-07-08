<?php
namespace Collecting\Api\Representation;

use Collecting\Entity\CollectingPrompt;
use Omeka\Api\Representation\AbstractRepresentation;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingPromptRepresentation extends AbstractRepresentation
{
    public function __construct(CollectingPrompt $resource, ServiceLocatorInterface $serviceLocator)
    {
        $this->resource = $resource;
        $this->setServiceLocator($serviceLocator);
    }

    public function jsonSerialize()
    {
        if ($property = $this->property()) {
            $property = $property->getReference();
        }
        return [
            'o:id' => $this->id(),
            'o-module-collecting:type' => $this->type(),
            'o-module-collecting:text' => $this->text(),
            'o-module-collecting:input_type' => $this->inputType(),
            'o-module-collecting:select_options' => $this->selectOptions(),
            'o-module-collecting:media_type' => $this->mediaType(),
            'o:property' => $property,
        ];
    }

    public function id()
    {
        return $this->resource->getId();
    }

    public function type()
    {
        return $this->resource->getType();
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

    public function mediaType()
    {
        return $this->resource->getMediaType();
    }

    public function property()
    {
        return $this->getAdapter('properties')
            ->getRepresentation($this->resource->getProperty());
    }
}
