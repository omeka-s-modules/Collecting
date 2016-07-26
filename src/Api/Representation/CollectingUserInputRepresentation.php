<?php
namespace Collecting\Api\Representation;

use Collecting\Api\Representation\CollectingUserPromptRepresentation;
use Collecting\Entity\CollectingInput;
use Omeka\Api\Representation\AbstractRepresentation;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingUserInputRepresentation extends AbstractRepresentation
{
    public function __construct(CollectingUserInput $resource, ServiceLocatorInterface $serviceLocator)
    {
        $this->resource = $resource;
        $this->setServiceLocator($serviceLocator);
    }

    public function jsonSerialize()
    {
        if ($item = $this->item()) {
            $item = $item->getReference();
        }
        return [
            'o:id' => $this->id(),
            'o-module-collecting:item' => $item,
            'o-module-collecting:user_prompt' => $this->userPrompt(),
            'o-module-collecting:text' => $this->text(),
        ];
    }

    public function id()
    {
        return $this->resource->getId();
    }

    public function item()
    {
        return $this->getAdapter('collecting_items')
            ->getRepresentation($this->resource->getItem());
    }

    public function userPrompt()
    {
        return new CollectingUserPromptRepresentation($this->resource->getUserPrompt(), $this->getServiceLocator());
    }

    public function text()
    {
        return $this->resource->getText();
    }
}
