<?php
namespace Collecting\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class CollectingItemRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-collecting:Item';
    }

    public function getJsonLd()
    {
        if ($item = $this->item()) {
            $item = $item->getReference();
        }
        if ($form = $this->form()) {
            $form = $form->getReference();
        }
        return [
            'o:item' => $item,
            'o-module-collecting:form' => $form,
            'o-module-collecting:input' => $this->inputs(),
        ];
    }

    public function item()
    {
        return $this->getAdapter('items')
            ->getRepresentation($this->resource->getItem());
    }

    public function form()
    {
        return $this->getAdapter('collecting_forms')
            ->getRepresentation($this->resource->getForm());
    }

    public function inputs()
    {
        $inputs = [];
        foreach ($this->resource->getInputs() as $input) {
            $inputs[]= new CollectingInputRepresentation($input, $this->getServiceLocator());
        }
        return $inputs;
    }
}
