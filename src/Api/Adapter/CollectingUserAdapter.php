<?php
namespace Collecting\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class CollectingUserAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'collecting_users';
    }

    public function getRepresentationClass()
    {
        return 'Collecting\Api\Representation\CollectingUserRepresentation';
    }

    public function getEntityClass()
    {
        return 'Collecting\Entity\CollectingUser';
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        $invalidOperations = [
            Request::CREATE, Request::UPDATE,
            Request::BATCH_CREATE, Request::DELETE,
        ];
        if (in_array($request->getOperation(), $invalidOperations)) {
            $errorStore->addError('o-module-collecting:user', 'Cannot create, update, or delete a collecting user.');
        }
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {}

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {}

    public function buildQuery(QueryBuilder $qb, array $query)
    {}
}
