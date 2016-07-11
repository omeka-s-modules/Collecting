<?php
namespace Collecting\Api\Adapter;

use Collecting\Entity\CollectingInput;
use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;

class CollectingItemAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'collecting_items';
    }

    public function getRepresentationClass()
    {
        return 'Collecting\Api\Representation\CollectingItemRepresentation';
    }

    public function getEntityClass()
    {
        return 'Collecting\Entity\CollectingItem';
    }

    public function validateRequest(Request $request, ErrorStore $errorStore)
    {
        if (Request::CREATE !== $request->getOperation()) {
            $errorStore->addError('o-module-collecting:item', 'Cannot update a collecting item.');
        }
    }

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore)
    {
        $data = $request->getContent();
        if (isset($data['o:item']['o:id'])) {
            $entity->setItem($this->getEntityManager()->getReference(
                'Omeka\Entity\Item',
                $data['o:item']['o:id']
            ));
        }
        if (isset($data['o-module-collecting:form']['o:id'])) {
            $entity->setForm($this->getEntityManager()->getReference(
                'Collecting\Entity\CollectingForm',
                $data['o-module-collecting:form']['o:id']
            ));
        }
        foreach ($data['o-module-collecting:input'] as $inputData) {
            $input = new CollectingInput;
            $input->setItem($entity);
            if (isset($inputData['o-module-collecting:prompt'])) {
                $input->setPrompt($this->getEntityManager()->getReference(
                    'Collecting\Entity\CollectingPrompt',
                    $inputData['o-module-collecting:prompt']
                ));
            }
            if (isset($inputData['o-module-collecting:text'])
                && '' !== trim($inputData['o-module-collecting:text'])
            ) {
                $input->setText($inputData['o-module-collecting:text']);
            }
            $entity->getInputs()->add($input);
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (!$entity->getItem()) {
            $errorStore->addError('o:item', 'A collecting item must be assigned an item on creation.');
        }
        if (!$entity->getForm()) {
            $errorStore->addError('o-module-collecting:form', 'A collecting item must be assigned a form on creation.');
        }
        foreach ($entity->getInputs() as $input) {
            if (!$input->getPrompt()) {
                $errorStore->addError('o-module-collecting:prompt', 'A collecting input must be assigned a prompt on creation.');
            }
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {}
}
