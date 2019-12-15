<?php
namespace Collecting\Controller\Site;

use Collecting\Api\Representation\CollectingFormRepresentation;
use Collecting\Api\Representation\CollectingItemRepresentation;
use Collecting\MediaType\Manager;
use Omeka\Api\Exception\NotFoundException;
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

        /** @var \Collecting\Api\Representation\CollectingFormRepresentation $cForm */
        $cForm = $this->api()
            ->read('collecting_forms', $this->params('form-id'))
            ->getContent();

        $form = $cForm->getForm(true);
        // Add the form only if user has rights to contribute.
        if ($form) {
            if (empty($form)) {
                return $this->redirect()->toRoute('site', [], true);
            }
        }

        // TODO Improve checking.

        $post = $this->params()->fromPost();
        $form->setData($post);
        if ($form->isValid()) {
            list($itemData, $cItemData) = $this->getPromptData($cForm);

            // Temporarily give the user permission to create the Omeka and
            // Collecting items. This gives all roles all privileges to all
            // resources, which _should_ be safe since we're only passing
            // mediated data.
            $this->acl->allow();

            // Create the Omeka item.
            $visibility = $this->siteSettings()->get('collecting_visibility', 'private');
            $itemData['o:is_public'] = $visibility === 'logged'
                ? (bool) $this->identity()
                : $visibility === 'public';
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

                    $page = $this->collectingPage();
                    if ($page) {
                        if ($message = $cForm->successText()) {
                            $message = new \Omeka\Stdlib\Message($message);
                            $message->setEscapeHtml(false);
                        } else {
                            $message = $this->translate('Form successfully submitted!'); // @translate
                        }
                        $this->messenger()->addSuccess($message);
                        return $this->redirect()->toRoute('site/page', ['site-slug' => $page->site()->slug(), 'page-slug' => $page->slug()], true);
                    }

                    return $this->redirect()->toRoute('site/collecting', ['form-id' => $cForm->id(), 'action' => 'success'], true);
                }
            }

            // Out of an abundance of caution, revert back to default permissions.
            $this->acl->removeAllow();
        } else {
            $this->messenger()->addErrors($form->getMessages());
        }

        $this->prepareRender();

        $view = new ViewModel;
        $view->setVariable('cForm', $cForm);
        $view->setVariable('page', $this->collectingPage());
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
        $matches = [];
        foreach ($this->params()->fromPost() as $key => $value) {
            if (preg_match('/^prompt_(\d+)$/', $key, $matches)) {
                $postedPrompts[$matches[1]] = $value;
            }
        }

        $identity = $this->identity();
        $api = $this->api();

        $itemData = [];
        $cItemData = [];
        $inputData = [];

        // Note that we're iterating the known prompts, not the ones submitted
        // with the form. This way we accept only valid prompts.
        /** @var \Collecting\Api\Representation\CollectingPromptRepresentation $prompt */
        foreach ($cForm->prompts() as $prompt) {
            $promptType = $prompt->type();
            if (!isset($postedPrompts[$prompt->id()])) {
                // This prompt was not found in the POSTed data.
                // Check if this is default metadata, not passed to the form.
                if ($promptType === 'metadata') {
                    switch ($prompt->inputType()) {
                        case 'resource_class':
                            $resourceClass = $api->searchOne('resource_classes', ['term' => $prompt->selectOptions()])->getContent();
                            if ($resourceClass) {
                                $itemData['o:resource_class'] = ['o:id' => $resourceClass->id()];
                            }
                            break;
                        case 'resource_template':
                            try {
                                $resourceTemplate = $api->read('resource_templates', ['id' => $prompt->selectOptions()])->getContent();
                                $itemData['o:resource_template'] = ['o:id' => $resourceTemplate->id()];
                            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                            }
                            break;
                    }
                }
                continue;
            }

            $value = $postedPrompts[$prompt->id()];
            $inputType = $prompt->inputType();

            $isMultiple = $prompt->multiple();

            // Media is an exception managed by another array.
            if ($promptType === 'media') {
                $postedValues = [''];
            } else {
                $postedValues = $isMultiple ? $value : [$value];
                // Do not save empty inputs.
                $postedValues = array_unique(array_filter(array_map('trim', $postedValues ?: []), 'strlen'));
            }
            foreach ($postedValues as $value) switch ($promptType) {
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
                        case 'value_suggest':
                            if (substr($prompt->selectOptions(), -7) === ':record') {
                                if (!preg_match('~^<a href="(.+)" target="_blank">\s*(.+)\s*</a>$~', $value, $matches)) {
                                    break;
                                }
                                $record = json_decode(htmlspecialchars_decode($matches[1]), true);
                                if (!$record) {
                                    break;
                                }
                                foreach ($record as $key => $v) {
                                    // Strip tags of each value for security.
                                    if (in_array($key, ['o:resource_class'])) {
                                        $itemData[$key] = array_map('strip_tags', $v);
                                    } else {
                                        foreach ($v as $vv) {
                                            $itemData[$key][] = array_map('strip_tags', $vv);
                                        }
                                    }
                                }
                            } else {
                                // Sometime, the uri is absent (for example isbn
                                // reference). The uri can be removed in admin too.
                                // TODO Move the validation for value_suggest into the prompt element. Note: the query part of the url is removed, because it is never used in uri and is a security issue.
                                if (preg_match('~^<a href="(https?://[^\s()<>"\?]+)" target="_blank">\s*(.+)\s*</a>$~', $value, $matches)) {
                                    $itemData[$propertyTerm][] = [
                                        'type' => $prompt->selectOptions(),
                                        'property_id' => $propertyId,
                                        '@id' => $matches[1],
                                        'o:label' => $matches[2],
                                    ];
                                } else {
                                    $itemData[$propertyTerm][] = [
                                        'type' => $prompt->selectOptions(),
                                        'property_id' => $propertyId,
                                        '@value' => $value,
                                    ];
                                }
                            }
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
                    $inputData[] = [
                        'o-module-collecting:prompt' => $prompt->id(),
                        'o-module-collecting:text' => $value,
                    ];
                    break;
                case 'user_name':
                    $cItemData['o-module-collecting:user_name'] = $identity
                        ? $identity->getName()
                        : $value;
                    break;
                case 'user_email':
                    $cItemData['o-module-collecting:user_email'] = $identity
                        ? $identity->getEmail()
                        : $value;
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
     * Append css and js in the specific submit page when there are errors.
     *
     * @see \Collecting\Site\BlockLayout\Collecting::prepareRender()
     *
     * @param ViewModel $view
     */
    protected function prepareRender()
    {
        $viewHelpers = $this->viewHelpers();
        $assetUrl = $viewHelpers->get('assetUrl');
        $headLink = $viewHelpers->get('headLink');
        $headScript = $viewHelpers->get('headScript');
        $url = $viewHelpers->get('url');

        $headLink->appendStylesheet($assetUrl('css/collecting.css', 'Collecting'));
        $headScript->appendFile($assetUrl('js/collecting-block.js', 'Collecting'), 'text/javascript', ['defer' => 'defer']);

        // TODO Append value suggest js only if a property uses it.
        // To check if ValueSuggest is available, just try to get the routed url.
        try {
            $proxyUrl = $url('site/value-suggest/proxy', [], true);
        } catch (\Exception $e) {
            return;
        }

        $escapeJs = $viewHelpers->get('escapeJs');

        $headLink()
            ->appendStylesheet($assetUrl('css/value-suggest.css', 'ValueSuggest'));
        $headScript()
            ->appendFile($assetUrl('js/jQuery-Autocomplete/1.2.26/jquery.autocomplete.min.js', 'ValueSuggest'), 'text/javascript', ['defer' => 'defer'])
            ->appendFile($assetUrl('js/value-suggest.js', 'ValueSuggest'), 'text/javascript', ['defer' => 'defer'])
            ->appendScript(sprintf(
                'var valueSuggestProxyUrl = "%s";',
                $escapeJs($proxyUrl)
            ));
    }

    /**
     * Get the page where the collecting form is from the hidden value "page".
     *
     * @return \Omeka\Api\Representation\SitePageRepresentation|null
     */
    protected function collectingPage()
    {
        if (!$this->siteSettings()->get('collecting_redirect_current', false)) {
            return null;
        }

        $pageId = (int) $this->params()->fromPost('page');
        if (empty($pageId)) {
            return null;
        }

        try {
            $page = $this->api()->read('site_pages', ['id' => $pageId])->getContent();
        } catch (NotFoundException $e) {
            $page = null;
        }
        return $page;
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
            '<p>'
            . $this->translate('You submitted the following data on %s using the form “%s” on the site “%s”: %s') // @translate
            . '</p>',
            $i18nHelper->dateFormat($cItem->item()->created(), 'long'),
            $cItem->form()->label(),
            $cItem->form()->site()->title(),
            $cItem->form()->site()->siteUrl(null, true)
        );
        $messageContent .= $partialHelper('common/collecting-item-inputs', ['cItem' => $cItem]);
        $messageContent .= '<p>'
            . $this->translate('(All data you submitted was saved, even if you do not see it here.)') // @translate
            . '</p>';

        $messagePart = new MimePart($messageContent);
        $messagePart->setType('text/html');

        $body = new MimeMessage;
        $body->addPart($messagePart);

        $message = $this->mailer()->createMessage()
            ->addTo($cItem->userEmail(), $cItem->userName())
            ->setSubject($this->translate('Thank you for your submission')) // @translate
            ->setBody($body);
        $this->mailer()->send($message);
    }
}
