<?php
namespace Collecting\Form\Element;

/**
 * Flag a prompt element as multiple or single.
 */
trait PromptIsMultipleTrait
{
    protected $multiple = false;

    public function setIsMultiple($multiple)
    {
        $this->multiple = (bool) $multiple;
        $this->setAttribute('data-multiple', $this->multiple);
        return $this;
    }
}
