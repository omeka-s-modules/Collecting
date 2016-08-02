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
            'o-module-collecting:anon' => $this->anon(),
            'o-module-collecting:input' => $this->inputs(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/collecting/item/id',
            [
                'site-slug' => $this->form()->site()->slug(),
                'controller' => $this->getControllerName(),
                'action' => $action,
                'form-id' => $this->form()->id(),
                'item-id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
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

    public function collectingUser()
    {
        return $this->getAdapter('collecting_users')
            ->getRepresentation($this->resource->getCollectingUser());
    }

    public function anon()
    {
        return $this->resource->getAnon();
    }

    public function created()
    {
        return $this->resource->getCreated();
    }

    public function modified()
    {
        return $this->resource->getModified();
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
