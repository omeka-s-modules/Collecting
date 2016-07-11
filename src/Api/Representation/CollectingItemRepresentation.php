<?php
namespace Collecting\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class CollectingItemRepresentation extends AbstractEntityRepresentation
{
    public function getJsonLdType()
    {
        return 'o-module-collecting:Item';
    }

    public function getJsonLd()
    {
        return [];
    }
}
