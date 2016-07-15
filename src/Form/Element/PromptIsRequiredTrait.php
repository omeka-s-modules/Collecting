<?php
namespace Collecting\Form\Element;;

trait PromptIsRequiredTrait
{
    protected $required = false;

    public function setIsRequired($required)
    {
        $this->required = (bool) $required;
        return $this;
    }

}
