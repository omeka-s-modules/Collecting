<?php
namespace Collecting\Form\Element;

use Zend\Form\Element;
use Zend\Http\Client;
use Zend\InputFilter\InputProviderInterface;

/**
 * A form element used to separate prompts.
 */
class PromptSeparator extends Element
{
    protected $attributes = [
        'type' => 'promptSeparator',
    ];
}
