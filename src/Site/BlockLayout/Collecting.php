<?php
namespace Collecting\Site\BlockLayout;

use Omeka\Api\Representation\SiteRepresentation;
use Omeka\Api\Representation\SitePageBlockRepresentation;
use Omeka\Entity\SitePageBlock;
use Omeka\Site\BlockLayout\AbstractBlockLayout;
use Omeka\Stdlib\ErrorStore;
use Zend\View\Renderer\PhpRenderer;

class Collecting extends AbstractBlockLayout
{
    public function getLabel()
    {
        return 'Collecting'; // @translate
    }

    public function onHydrate(SitePageBlock $block, ErrorStore $errorStore)
    {}

    public function form(PhpRenderer $view, SiteRepresentation $site,
        SitePageBlockRepresentation $block = null)
    {
        return $view->partial('common/block-layout/collecting-block-form');
    }

    public function render(PhpRenderer $view, SitePageBlockRepresentation $block)
    {}
}
