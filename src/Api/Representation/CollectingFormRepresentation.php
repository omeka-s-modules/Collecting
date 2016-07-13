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
        if ($itemSet = $this->itemSet()) {
            $itemSet = $itemSet->getReference();
        }
        return [
            'o-module-collecting:label' => $this->label(),
            'o-module-collecting:description' => $this->description(),
            'o:site' => $site,
            'o:item_set' => $itemSet,
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

    public function itemSet()
    {
        return $this->getAdapter('item_sets')
            ->getRepresentation($this->resource->getItemSet());
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

        $url = $this->getViewHelper('Url');

        $form = new Form(sprintf('collecting_form_%s', $this->id()));
        $this->form = $form; // cache the form
        $form->setAttribute('enctype', 'multipart/form-data');
        $form->setAttribute('action', $url('site/collecting', [
            'action' => 'submit',
            'form-id' => $this->id(),
        ], true));

        // Add the CSRF element first so getInputFilter() knows about it.
        $csrfElement = (new Element\Csrf(sprintf('csrf_%s', $this->id())))
            ->setCsrfValidatorOptions(['timeout' => 3600]);
        $form->add($csrfElement);

        foreach ($this->prompts() as $prompt) {
            $name = sprintf('prompt_%s', $prompt->id());
            switch ($prompt->type()) {
                case 'property':
                    // Note that there's no break here. When building the form
                    // we handle property and input prompts the same.
                case 'input':
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
                    switch ($prompt->mediaType()) {
                        case 'upload':
                            // Note that the file index maps to the prompt ID.
                            $fileElementName = sprintf('file[%s]', $prompt->id());
                            $element = (new Element\File($fileElementName))
                                ->setLabel($prompt->text());
                            $form->add($element);
                            break;
                        case 'url':
                            $urlElementName = sprintf('ingest_url_%s', $prompt->id());
                            $element = (new Element\Url($urlElementName))
                                ->setLabel($prompt->text());
                            $form->add($element);
                            break;
                        case 'html':
                            break;
                        default:
                            continue 2;
                    }
                    // A hidden element marks the existence of this prompt.
                    $form->add(new Element\Hidden($name));
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    continue 2;
            }
        }

        $form->add((new Element\Submit('submit'))->setValue('Submit')); // @translate
        return $form;
    }
}
