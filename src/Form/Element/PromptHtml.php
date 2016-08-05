<?php
namespace Collecting\Form\Element;

use Zend\Form\Element;
use Zend\Http\Client;
use Zend\InputFilter\InputProviderInterface;

/**
 * A form element used to add markup to the form.
 */
class PromptHtml extends Element
{
    protected $attributes = [
        'type' => 'promptHtml',
    ];
}
