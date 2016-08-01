<?php
namespace Collecting\Api\Representation;

use Collecting\Form\Element;
use Omeka\Api\Representation\AbstractEntityRepresentation;
use Zend\Form\Form;
use Zend\Http\PhpEnvironment\RemoteAddress;

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
                'form-id' => $this->id(),
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
        $form->setAttribute('action', $url('site/collecting', [
            'form-id' => $this->id(),
            'action' => 'submit',
        ], true));

        foreach ($this->prompts() as $prompt) {
            $name = sprintf('prompt_%s', $prompt->id());
            switch ($prompt->type()) {
                // Note that there's no break here. When building the form we
                // handle property, input, and user prompts the same.
                case 'property':
                case 'input':
                case 'user':
                    switch ($prompt->inputType()) {
                        case 'text':
                            $element = new Element\PromptText($name);
                            break;
                        case 'textarea':
                            $element = new Element\PromptTextarea($name);
                            break;
                        case 'select':
                            $selectOptions = explode(PHP_EOL, $prompt->selectOptions());
                            $element = new Element\PromptSelect($name);
                            $element->setEmptyOption('Please choose one...') // @translate
                                ->setValueOptions(array_combine($selectOptions, $selectOptions));
                            break;
                        default:
                            // Invalid prompt input type. Do nothing.
                            continue 2;
                    }
                    $label = ($prompt->property() && !$prompt->text())
                        ? $prompt->property()->label()
                        : $prompt->text();
                    $element->setLabel($label)
                        ->setIsRequired($prompt->required());
                    $form->add($element);
                    break;
                case 'media':
                    switch ($prompt->mediaType()) {
                        case 'upload':
                            // A hidden element marks the existence of this prompt.
                            $form->add([
                                'type' => 'hidden',
                                'name' => $name,
                            ]);
                            // Note that the file index maps to the prompt ID.
                            $form->add([
                                'type' => 'file',
                                'name' => sprintf('file[%s]', $prompt->id()),
                                'options' => [
                                    'label' => $prompt->text(),
                                ],
                            ]);
                            break;
                        case 'url':
                            $element = new Element\PromptUrl($name);
                            $element->setLabel($prompt->text())
                                ->setIsRequired($prompt->required());
                            $form->add($element);
                            break;
                        case 'html':
                            $element = new Element\PromptTextarea($name);
                            $element->setLabel($prompt->text())
                                ->setAttribute('id', $name)
                                ->setIsRequired($prompt->required());
                            $form->add($element);
                            // Enable the CKEditor HTML text editor.
                            $this->getViewHelper('ckEditor')->__invoke();
                            $this->getViewHelper('headScript')
                                ->appendScript('$(document).ready(function() {$("#' . $name . '").ckeditor();});');
                            break;
                        default:
                            // Invalid prompt media type. Do nothing.
                            continue 2;
                    }
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    continue 2;
            }
        }

        $siteSettings = $this->getServiceLocator()->get('Omeka\SiteSettings');

        // Add the terms of service if provided in site settings.
        $tos = $siteSettings->get('collecting_tos');
        if ($tos) {
            $tosUrl = $url('site/collecting', [
                'form-id' => $this->id(),
                'action' => 'tos',
            ], true);
            $form->add([
                'type' => 'checkbox',
                'name' => sprintf('tos_accept_%s', $this->id()),
                'options' => [
                    'label' => 'I accept the <a href="' . $tosUrl . '" target="_blank" style="text-decoration: underline;">Terms of Service</a>',
                    'label_options' => [
                        'disable_html_escape' => true,
                    ],
                    'use_hidden_element' => false,
                ],
            ]);
        }

        // Add reCAPTCHA protection if keys are provided in site settings.
        $siteKey = $siteSettings->get('collecting_recaptcha_site_key');
        $secretKey = $siteSettings->get('collecting_recaptcha_secret_key');
        if ($siteKey && $secretKey) {
            $element = $this->getServiceLocator()
                ->get('FormElementManager')
                ->get('Omeka\Form\Element\Recaptcha', [
                    'site_key' => $siteKey,
                    'secret_key' => $secretKey,
                    'remote_ip' => (new RemoteAddress)->getIpAddress(),
                ]);
            $form->add($element);
        }

        $form->add([
            'type' => 'csrf',
            'name' => sprintf('csrf_%s', $this->id()),
            'options' => [
                'csrf_options' => ['timeout' => 3600],
            ],
        ]);
        $form->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Submit',
            ],
        ]);
        return $form;
    }
}
