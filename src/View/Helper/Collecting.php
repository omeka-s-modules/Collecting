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
}
