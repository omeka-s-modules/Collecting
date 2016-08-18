<?php
namespace Collecting\MediaType;

use Collecting\Api\Representation\CollectingPromptRepresentation;
use Zend\Form\Form;
use Zend\I18n\Translator\TranslatorInterface;
use Zend\View\Renderer\PhpRenderer;

class Fallback implements MediaTypeInterface
{
    protected $name;

    protected $translator;

    public function __construct($name, TranslatorInterface $translator)
    {
        $this->name = $name;
        $this->translator = $translator;
    }

    public function getLabel()
    {
        return sprintf('%s [%s]', $this->translator->translate('Unknown'), $this->name);
    }

    public function prepareForm(PhpRenderer $view)
    {}

    public function form(Form $form, CollectingPromptRepresentation $prompt, $name)
    {}

    public function itemData(array $itemData, $postedPrompt,
        CollectingPromptRepresentation $prompt
    ) {
        return $itemData;
    }
}
