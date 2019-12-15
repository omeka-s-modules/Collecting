<?php
namespace Collecting\Controller\Site;

use Collecting\Api\Representation\CollectingFormRepresentation;
use Collecting\Api\Representation\CollectingItemRepresentation;
use Collecting\MediaType\Manager;
use Omeka\Permissions\Acl;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
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
            list($itemData, $cItemData) = $this->getPromptData($cForm);

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

            if ($response) {
                $item = $response->getContent();

                // Create the Collecting item.
                $cItemData['o:item'] = ['o:id' => $item->id()];
                $cItemData['o-module-collecting:form'] = ['o:id' => $cForm->id()];

                if ('user' === $cForm->anonType()) {
                    // If the form has the "user" anonymity type, the item's
                    // default anonymous flag is "false" because the related
                    // prompt ("User Public") is naturally public.
                    $cItemData['o-module-collecting:anon']
                        = $this->params()->fromPost(sprintf('anon_%s', $cForm->id()), false);
                }

                $response = $this->api($form)->create('collecting_items', $cItemData);

                if ($response) {
                    $cItem = $response->getContent();

                    // Send a submission email if the user opts-in and provides
                    // an email address.
                    $sendEmail = $this->params()->fromPost(sprintf('email_send_%s', $cForm->id()), false);
                    if ($sendEmail && $cItem->userEmail()) {
                        $this->sendSubmissionEmail($cForm, $cItem);
                    }

                    return $this->redirect()->toRoute(null, ['action' => 'success'], true);
                }
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
    {
        $cForm = $this->api()
            ->read('collecting_forms', $this->params('form-id'))
            ->getContent();
        $view = new ViewModel;
        $view->setVariable('cForm', $cForm);
        return $view;
    }

    public function tosAction()
    {
        $response = $this->getResponse();
        $response->getHeaders()->addHeaderLine('Content-Type', 'text/plain; charset=utf-8');
        $response->setContent($this->siteSettings()->get('collecting_tos'));
        return $response;
    }

    public function itemShowAction()
    {
        $site = $this->currentSite();
        $cItem = $this->api()
            ->read('collecting_items', $this->params('item-id'))->getContent();

        $view = new ViewModel;
        $view->setVariable('site', $site);
        $view->setVariable('cItem', $cItem);
        return $view;
    }

    /**
     * Get the prompt data needed to create the Omeka and Collecting items.
     *
     * @param CollectingFormRepresentation $cForm
     * @return array [itemData, cItemData]
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
        $cItemData = [];
        $inputData = [];

        // Note that we're iterating the known prompts, not the ones submitted
        // with the form. This way we accept only valid prompts.
        foreach ($cForm->prompts() as $prompt) {
            if (!isset($postedPrompts[$prompt->id()])) {
                // This prompt was not found in the POSTed data.
                continue;
            }
            $value = $postedPrompts[$prompt->id()];
            $inputType = $prompt->inputType();
            switch ($inputType) {
                case 'property':
                    $propertyTerm = $prompt->property()->term();
                    $propertyId = $prompt->property()->id();
                    switch ($inputType) {
                        case 'item':
                            $itemData[$propertyTerm][] = [
                                'type' => 'resource',
                                'property_id' => $propertyId,
                                'value_resource_id' => $value,
                            ];
                            break;
                        case 'custom_vocab':
                            $itemData[$propertyTerm][] = [
                                'type' => 'customvocab:' . $prompt->customVocab(),
                                'property_id' => $propertyId,
                                '@value' => $value,
                            ];
                            break;
                        case 'numeric:timestamp':
                        case 'numeric:interval':
                        case 'numeric:duration':
                        case 'numeric:integer':
                            $itemData[$propertyTerm][] = [
                                'type' => $inputType,
                                'property_id' => $propertyId,
                                '@value' => $value,
                            ];
                            break;
                        default:
                            $itemData[$propertyTerm][] = [
                                'type' => 'literal',
                                'property_id' => $propertyId,
                                '@value' => $value,
                            ];
                    }
                    // Note that there's no break here. We need to save all
                    // property types as inputs so the relationship between the
                    // prompt and the user input isn't lost.
                    // no break
                case 'input':
                case 'user_private':
                case 'user_public':
                    // Do not save empty inputs.
                    if ('' !== trim($value)) {
                        $inputData[] = [
                            'o-module-collecting:prompt' => $prompt->id(),
                            'o-module-collecting:text' => $value,
                        ];
                    }
                    break;
                case 'user_name':
                    $cItemData['o-module-collecting:user_name'] = $value;
                    break;
                case 'user_email':
                    $cItemData['o-module-collecting:user_email'] = $value;
                    break;
                case 'media':
                    $itemData = $this->mediaTypeManager->get($prompt->mediaType())
                        ->itemData($itemData, $value, $prompt);
                    break;
                default:
                    // Invalid prompt type. Do nothing.
                    break;
            }
        }

        $cItemData['o-module-collecting:input'] = $inputData;
        return [$itemData, $cItemData];
    }

    /**
     * Send a submission email.
     *
     * @param CollectingFormRepresentation $cForm
     * @param CollectingItemRepresentation $cItem
     */
    protected function sendSubmissionEmail(
        CollectingFormRepresentation $cForm,
        CollectingItemRepresentation $cItem
    ) {
        $i18nHelper = $this->viewHelpers()->get('i18n');
        $partialHelper = $this->viewHelpers()->get('partial');

        $messageContent = '';
        if ($cForm->emailText()) {
            $messageContent .= $cForm->emailText();
        }
        $messageContent .= sprintf(
            '<p>You submitted the following data on %s using the form “%s” on the site “%s”: %s</p>',
            $i18nHelper->dateFormat($cItem->item()->created(), 'long'),
            $cItem->form()->label(),
            $cItem->form()->site()->title(),
            $cItem->form()->site()->siteUrl(null, true)
        );
        $messageContent .= $partialHelper('common/collecting-item-inputs', ['cItem' => $cItem]);
        $messageContent .= '<p>(All data you submitted was saved, even if you do not see it here.)</p>';

        $messagePart = new MimePart($messageContent);
        $messagePart->setType('text/html');

        $body = new MimeMessage;
        $body->addPart($messagePart);

        $message = $this->mailer()->createMessage()
            ->addTo($cItem->userEmail(), $cItem->userName())
            ->setSubject($this->translate('Thank you for your submission'))
            ->setBody($body);
        $this->mailer()->send($message);
    }
}
