<?php
namespace Collecting\Api\Representation;

use Collecting\Api\Representation\CollectingPromptRepresentation;
use Collecting\Entity\CollectingInput;
use Omeka\Api\Representation\AbstractRepresentation;
use Zend\ServiceManager\ServiceLocatorInterface;

class CollectingInputRepresentation extends AbstractRepresentation
{
    public function __construct(CollectingInput $resource, ServiceLocatorInterface $serviceLocator)
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
            'o-module-collecting:prompt' => $this->prompt(),
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

    public function prompt()
    {
        return new CollectingPromptRepresentation($this->resource->getPrompt(), $this->getServiceLocator());
    }

    public function text()
    {
        return $this->resource->getText();
    }

    /**
     * Get the input text, ready for display.
     *
     * @return string
     */
    public function displayText()
    {
        $displayText = $this->text();
        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        if (!$acl->userIsAllowed($this->resource, 'view-collecting-input')) {
            $displayText = $this->getTranslator()->translate('[private]');
        }
        return $displayText;
    }
}
