<?php
namespace Collecting\MediaType;

use Collecting\Api\Representation\CollectingPromptRepresentation;
use Zend\Form\Form;
use Zend\Mvc\Controller\PluginManager;
use Zend\View\Renderer\PhpRenderer;

class Upload implements MediaTypeInterface
{
    protected $plugins;

    public function __construct(PluginManager $plugins)
    {
        $this->plugins = $plugins;
    }

    public function getLabel()
    {
        return 'Upload'; // @translate
    }

    public function prepareForm(PhpRenderer $view)
    {}

    public function form(Form $form, CollectingPromptRepresentation $prompt, $name)
    {
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
            'attributes' => [
                'required' => $prompt->required(),
            ],
        ]);
    }

    public function itemData(array $itemData, $postedPrompt,
        CollectingPromptRepresentation $prompt
    ) {
        $files = $this->plugins->get('params')->fromFiles('file');
        if ($prompt->required()
            || (!$prompt->required()
                && isset($files[$prompt->id()])
                && UPLOAD_ERR_NO_FILE !== $files[$prompt->id()]['error']
            )
        ) {
            $itemData['o:media'][$prompt->id()] = [
                'o:ingester' => 'upload',
                'file_index' => $prompt->id(),
            ];
        }
        return $itemData;
    }
}
