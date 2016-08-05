<?php
namespace Collecting\View\Helper;

use Zend\View\Helper\AbstractHelper;

class CollectingPrepareForm extends AbstractHelper
{
    public function __invoke()
    {
        // Enable the CKEditor HTML text editors.
        $this->getView()->ckEditor();

        // Prepare the reCAPTCHA element.
        $this->getView()->prepareRecaptcha();

        // Map the HTML element type to the view helper that renders it.
        $this->getView()->formElement()->addType('promptHtml', 'formPromptHtml');
    }
}
