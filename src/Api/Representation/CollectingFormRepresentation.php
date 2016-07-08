<?php
namespace Collecting\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;
use Zend\Form\Element;
use Zend\Form\Form;

class CollectingFormRepresentation extends AbstractEntityRepresentation
{
    /**
     * @var Form
     */
    protected $form;

    public function getControllerName()
    {
        return 'collecting';
    }

    public function getJsonLdType()
    {
        return 'o-module-collecting:Form';
    }

    public function getJsonLd()
    {
        if ($site = $this->site()) {
            $site = $site->getReference();
        }
        return [
            'o:site' => $site,
            'o-module-collecting:label' => $this->label(),
            'o-module-collecting:description' => $this->description(),
            'o-module-collecting:prompt' => $this->prompts(),
        ];
    }

    public function adminUrl($action = null, $canonical = false)
    {
        $url = $this->getViewHelper('Url');
        return $url(
            'admin/site/slug/collecting/id',
            [
                'site-slug' => $this->site()->slug(),
                'controller' => $this->getControllerName(),
                'action' => $action,
                'id' => $this->id(),
            ],
            ['force_canonical' => $canonical]
        );
    }

    public function label()
    {
        return $this->resource->getLabel();
    }

    public function description()
    {
        return $this->resource->getDescription();
    }

    public function owner()
    {
        return $this->getAdapter('users')
            ->getRepresentation($this->resource->getOwner());
    }

    public function site()
    {
        return $this->getAdapter('sites')
            ->getRepresentation($this->resource->getSite());
    }

    public function prompts()
    {
        $prompts = [];
        foreach ($this->resource->getPrompts() as $prompt) {
            $prompts[]= new CollectingPromptRepresentation($prompt, $this->getServiceLocator());
        }
        return $prompts;
    }

    /**
     * Get the object used to validate and render this form.
     *
     * @return Form
     */
    public function getForm()
    {
        if ($this->form) {
            return $this->form; // build the form object only once
        }

        $form = new Form;
        $this->form = $form; // cache the form
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->add(new Element\Csrf('csrf'));
        $form->add((new Element\Hidden('collecting_form'))->setValue($this->id()));

        foreach ($this->prompts() as $prompt) {
            switch ($prompt->type()) {
                case 'property':
                    // Note that there's no break here. When building the form
                    // we handle property and input prompts the same.
                case 'input':
                    $name = sprintf('prompt_%s', $prompt->id());
                    switch ($prompt->inputType()) {
                        case 'text':
                            $element = new Element\Text($name);
                            $form->add($element);
                            break;
                        case 'textarea':
                            $element = new Element\Textarea($name);
                            $form->add($element);
                            break;
                        case 'select':
                            $selectOptions = explode("\n", $prompt->selectOptions());
                            $element = (new Element\Select($name))
                                ->setEmptyOption('Please choose one...') // @translate
                                ->setValueOptions(array_combine($selectOptions, $selectOptions));
                            $form->add($element);
                            $form->getInputFilter()->get($name)->setRequired(false);
                            break;
                        default:
                            // Invalid prompt input type. Do nothing.
                            continue 2;
                    }
                    $label = (!$prompt->text() && $prompt->property())
                        ? $prompt->property()->label()
                        : $prompt->text();
                    $element->setLabel($label);
                    break;
                case 'media':
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    continue 2;
            }
        }

        $form->add((new Element\Submit('submit'))->setValue('Submit'));
        return $form;
    }
}
