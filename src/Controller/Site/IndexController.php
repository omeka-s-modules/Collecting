<?php
namespace Collecting\Controller\Site;

use Collecting\Api\Representation\CollectingFormRepresentation;
use Collecting\MediaType\Manager;
use Omeka\Permissions\Acl;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    /**
     * @var Acl
     */
    protected $acl;

    protected $mediaTypeManager;

    public function __construct(Acl $acl, Manager $mediaTypeManager)
    {
        $this->acl = $acl;
        $this->mediaTypeManager = $mediaTypeManager;
    }

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

            list($itemData, $inputData) = $this->getPromptData($cForm);

            // Temporarily give the user permission to create the Omeka and
            // Collecting items. This gives all roles all privileges to all
            // resources, which _should_ be safe since we're only passing
            // mediated data.
            $this->acl->allow();

            // Create the Omeka item.
            $itemData['o:is_public'] = false;
            $itemData['o:item_set'] = [
                'o:id' => $cForm->itemSet() ? $cForm->itemSet()->id() : null,
            ];
            $response = $this->api($form)
                ->create('items', $itemData, $this->params()->fromFiles());

            if ($response->isSuccess()) {
                $item = $response->getContent();

                // Create the Collecting item.
                $cItemData = [
                    'o:item' => ['o:id' => $item->id()],
                    'o-module-collecting:form' => ['o:id' => $cForm->id()],
                    'o-module-collecting:input' => $inputData,
                ];
                if ('user' === $cForm->anonType()) {
                    // If the form has the "user" anonymity type, the item's
                    // defualt anonymous flag is "false" becuase the related
                    // prompt ("User Public") is naturally public.
                    $cItemData['o-module-collecting:anon']
                        = $this->params()->fromPost(sprintf('anon_%s', $cForm->id()), false);
                }
                $cItem = $this->api($form)
                    ->create('collecting_items', $cItemData)->getContent();

                return $this->redirect()->toRoute(null, ['action' => 'success'], true);
            }

            // Out of an abundance of caution, revert back to default permissions.
            $this->acl->removeAllow();

        } else {
            $this->messenger()->addErrors($form->getMessages());
        }

        $view = new ViewModel;
        $view->setVariable('cForm', $cForm);
        return $view;
    }

    public function successAction()
    {}

    public function tosAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($this->siteSettings()->get('collecting_tos'));
        return $response;
    }

    /**
     * Get the prompt data needed to create the Omeka and Collecting items.
     *
     * @param CollectingFormRepresentation $cForm
     * @return array [itemData, inputData]
     */
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
                case 'user_private':
                case 'user_public':
                    // Do not save empty inputs.
                    if ('' !== trim($postedPrompts[$prompt->id()])) {
                        $inputData[] = [
                            'o-module-collecting:prompt' => $prompt->id(),
                            'o-module-collecting:text' => $postedPrompts[$prompt->id()],
                        ];
                    }
                    break;
                case 'media':
                    $itemData = $this->mediaTypeManager->get($prompt->mediaType())
                        ->itemData($itemData, $postedPrompts[$prompt->id()],
                            $prompt, $this->params());
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    break;
            }
        }

        return [$itemData, $inputData];
    }
}
