<?php
namespace Collecting\View\Helper;

use Collecting\Entity\CollectingPrompt;
use Zend\View\Helper\AbstractHelper;

class Collecting extends AbstractHelper
{
    public function types()
    {
        return CollectingPrompt::getTypes();
    }

    public function inputTypes()
    {
        return CollectingPrompt::getInputTypes();
    }

    public function mediaTypes()
    {
        return CollectingPrompt::getMediaTypes();
    }

    public function typeValue($key)
    {
        return isset($this->types()[$key]) ? $this->types()[$key] : null;
    }

    public function inputTypeValue($key)
    {
        return isset($this->inputTypes()[$key]) ? $this->inputTypes()[$key] : null;
    }

    public function mediaTypeValue($key)
    {
        return isset($this->mediaTypes()[$key]) ? $this->mediaTypes()[$key] : null;
    }

}
