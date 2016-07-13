<?php
namespace Collecting\Controller\Site;

use Collecting\Api\Representation\CollectingFormRepresentation;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function submitAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute('site', [], true);
        }
        $cForm = $this->api()
            ->read('collecting_forms', $this->params('form-id'))
            ->getContent();
        $form = $cForm->getForm();
        $form->setData($this->params()->fromPost());
        if ($form->isValid()) {
            $pData = $this->getPromptData($cForm);
            // Create the Omeka item.
            $itemData = $pData['itemData'];
            $itemData['o:item_set'] = [
                'o:id' => $cForm->itemSet() ? $cForm->itemSet()->id() : null,
            ];
            $response = $this->api($form)->create('items', $itemData, $this->params()->fromFiles());
            if ($response->isSuccess()) {
                // Create the Collecting item.
                $cItemData = [
                    'o:item' => ['o:id' => $response->getContent()->id()],
                    'o-module-collecting:form' => ['o:id' => $cForm->id()],
                    'o-module-collecting:input' => $pData['inputData'],
                ];;
                $response = $this->api($form)->create('collecting_items', $cItemData);
                $this->messenger()->addSuccess($this->translate('Form successfully submitted'));
                return $this->redirect()->toRoute(null, ['action' => 'success'], true);
            }
        } else {
            $this->messenger()->addErrors($form->getMessages());
        }
        $view->setVariable('cForm', $cForm);
        return $view;
    }

    public function successAction()
    {
        $cForm = $this->api()
            ->read('collecting_forms', $this->params('form-id'))
            ->getContent();
        $view = new ViewModel;
        $view->setVariable('cForm', $cForm);
        return $view;
    }

    protected function getPromptData(CollectingFormRepresentation $cForm)
    {
        // Derive the prompt IDs from the form names.
        $postedPrompts = [];
        foreach ($this->params()->fromPost() as $key => $value) {
            if (preg_match('/^prompt_(\d+)$/', $key, $matches)) {
                $postedPrompts[$matches[1]] = $value;
            }
        }

        $itemData = [];
        $inputData = [];

        // Note that we're iterating the known prompts, not the ones submitted
        // with the form. This way we accept only valid prompts.
        foreach ($cForm->prompts() as $prompt) {
            if (!isset($postedPrompts[$prompt->id()])) {
                // This prompt was not found in the POSTed data.
                continue;
            }
            switch ($prompt->type()) {
                case 'property':
                    $itemData[$prompt->property()->term()][] = [
                        'type' => 'literal',
                        'property_id' => $prompt->property()->id(),
                        '@value' => $postedPrompts[$prompt->id()],
                    ];
                    // Note that there's no break here. We need to save all
                    // property types as inputs so the relationship between the
                    // prompt and the user input isn't lost.
                case 'input':
                    // Do not save empty inputs.
                    if ('' !== trim($postedPrompts[$prompt->id()])) {
                        $inputData[] = [
                            'o-module-collecting:prompt' => $prompt->id(),
                            'o-module-collecting:text' => $postedPrompts[$prompt->id()],
                        ];
                    }
                    break;
                case 'media':
                    if ('upload' === $prompt->mediaType()) {
                        $itemData['o:media'][$prompt->id()] = [
                            'file_index' => $prompt->id(),
                            'o:ingester' => 'upload',
                        ];
                    } elseif ('url' === $prompt->mediaType()) {
                        $itemData['o:media'][$prompt->id()] = [
                            'ingest_url' => $this->params()->fromPost('ingest_url_' . $prompt->id()),
                            'o:ingester' => 'url',
                        ];
                    }
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    break;
            }
        }

        return [
            'itemData' => $itemData,
            'inputData' => $inputData,
        ];
    }
}
