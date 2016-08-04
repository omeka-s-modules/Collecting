<?php
namespace Collecting\Api\Representation;

use Collecting\Entity\CollectingPrompt;
use Omeka\Api\Representation\AbstractEntityRepresentation;

class CollectingItemRepresentation extends AbstractEntityRepresentation
{
    /**
     * @var array Cache of all inputs
     */
    protected $inputs;

    /**
     * @var array Cache of all inputs keyed by prompt type
     */
    protected $inputsByType;

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

    /**
     * Get all inputs.
     *
     * @return array
     */
    public function inputs()
    {
        $this->cacheInputs();
        return $this->inputs;
    }

    /**
     * Get inputs by prompt type.
     *
     * @param string $type
     * @return array
     */
    public function inputsByType($type)
    {
        $this->cacheInputs();
        return isset($this->inputsByType[$type]) ? $this->inputsByType[$type] : [];
    }

    /**
     * Cache inputs if not already cached.
     */
    protected function cacheInputs()
    {
        if (is_array($this->inputs)) {
            return; // already cached
        }

        $inputs = [];
        $inputsByType = array_map(function () {return [];}, CollectingPrompt::getTypes());
        $services = $this->getServiceLocator();
        foreach ($this->resource->getInputs() as $input) {
            $inputRep = new CollectingInputRepresentation($input, $services);
            $inputs[] = $inputRep;
            $inputsByType[$input->getPrompt()->getType()][] = $inputRep;
        }

        $this->inputs = $inputs;
        $this->inputsByType = $inputsByType;
    }
}
