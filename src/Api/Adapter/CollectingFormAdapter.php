<?php
namespace Collecting\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class CollectingFormAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'collecting_forms';
    }

    public function getRepresentationClass()
    {
        return 'Collecting\Api\Representation\CollectingFormRepresentation';
    }

    public function getEntityClass()
    {
        return 'Collecting\Entity\CollectingForm';
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {}

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {}

    public function buildQuery(QueryBuilder $qb, array $query)
    {}
}
