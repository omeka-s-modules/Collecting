<?php
namespace Collecting\Api\Adapter;

use Collecting\Entity\CollectingPrompt;
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

    public function hydrate(Request $request, EntityInterface $entity, ErrorStore $errorStore) {
        $data = $request->getContent();

        // Hydrate the owner.
        $this->hydrateOwner($request, $entity);

        // Hydrate the site. The site cannot be reassigned after creation.
        if (Request::CREATE === $request->getOperation()) {
            if (isset($data['o:site']['o:id'])) {
                $site = $this->getAdapter('sites')->findEntity($data['o:site']['o:id']);
                $entity->setSite($site);
            } else {
                $errorStore->addError('o:site', 'A collecting form must be assigned a site on creation.');
            }
        }

        // Hydrate the form data.
        if ($this->shouldHydrate($request, 'o-module-collecting:label')) {
            $entity->setLabel($request->getValue('o-module-collecting:label'));
        }
        if ($this->shouldHydrate($request, 'o-module-collecting:description')) {
            $entity->setDescription($request->getValue('o-module-collecting:description'));
        }

        // Hydrate the form prompts.
        if ($this->shouldHydrate($request, 'o-module-collecting:prompt')) {
            $position = 1;
            $promptsToRetain = [];
            $propertyAdapter = $this->getAdapter('properties');
            foreach ($request->getValue('o-module-collecting:prompt', []) as $promptData) {
                if (!$promptData = $this->validatePromptData($promptData)) {
                    // Do not hydrate an invlaid prompt.
                    continue;
                }
                if ($entity->getPrompts()->containsKey($promptData['o:id'])) {
                    // Update an existing prompt.
                    $prompt = $entity->getPrompts()->get($promptData['o:id']);
                } else {
                    // Create a new prompt.
                    $prompt = new CollectingPrompt;
                    $prompt->setForm($entity);
                    $prompt->setType($promptData['o-module-collecting:type']);
                    $entity->getPrompts()->add($prompt);
                }
                $prompt->setPosition($position++);
                $prompt->setText($promptData['o-module-collecting:text']);
                $prompt->setInputType($promptData['o-module-collecting:input_type']);
                $prompt->setSelectOptions($promptData['o-module-collecting:select_options']);
                $prompt->setMediaType($promptData['o-module-collecting:media_type']);
                if (is_numeric($promptData['o:property']['o:id'])) {
                    $property = $propertyAdapter->findEntity($promptData['o:property']['o:id']);
                    $prompt->setProperty($property);
                }
                $promptsToRetain[] = $prompt;
            }
            // Delete prompts not included in the request.
            foreach ($entity->getPrompts() as $prompt) {
                if (!in_array($prompt, $promptsToRetain)) {
                    $entity->getPrompts()->removeElement($prompt);
                }
            }
        }
    }

    /**
     * Validate prompt data.
     *
     * @param array $promptData
     * @return bool Returns false if the prompt data does not validate.
     */
    protected function validatePromptData($data)
    {
        if (!is_array($data)) {
            return false;
        }

        // Set the default data array.
        $validatedData = [
            'o:id' => null,
            'o-module-collecting:type' => null,
            'o-module-collecting:text' => null,
            'o-module-collecting:input_type' => null,
            'o-module-collecting:select_options' => null,
            'o-module-collecting:media_type' => null,
            'o:property' => ['o:id' => null],
        ];

        // Populate the default data array with the passed data.
        if (isset($data['o:id']) && is_numeric($data['o:id'])) {
            $validatedData['o:id'] = $data['o:id'];
        }
        if (isset($data['o-module-collecting:type']) && '' !== trim($data['o-module-collecting:type'])) {
            $validatedData['o-module-collecting:type'] = $data['o-module-collecting:type'];
        }
        if (isset($data['o-module-collecting:text']) && '' !== trim($data['o-module-collecting:text'])) {
            $validatedData['o-module-collecting:text'] = $data['o-module-collecting:text'];
        }
        if (isset($data['o-module-collecting:input_type']) && '' !== trim($data['o-module-collecting:input_type'])) {
            $validatedData['o-module-collecting:input_type'] = $data['o-module-collecting:input_type'];
        }
        if (isset($data['o-module-collecting:media_type']) && '' !== trim($data['o-module-collecting:media_type'])) {
            $validatedData['o-module-collecting:media_type'] = $data['o-module-collecting:media_type'];
        }
        if (isset($data['o:property']['o:id']) && is_numeric($data['o:property']['o:id'])) {
            $validatedData['o:property']['o:id'] = $data['o:property']['o:id'];
        }

        // Do the validation.
        switch ($validatedData['o-module-collecting:type']) {
            case 'property':
                if (null === $validatedData['o:property']['o:id']) {
                    return false;
                }
                if (null === $validatedData['o-module-collecting:input_type']) {
                    return false;
                }
                break;
            case 'media':
                if (null === $validatedData['o-module-collecting:media_type']) {
                    return false;
                }
                break;
            case 'input':
                if (null === $validatedData['o-module-collecting:text']) {
                    return false;
                }
                if (null === $validatedData['o-module-collecting:input_type']) {
                    return false;
                }
                break;
            default:
                // Invalid or no prompt type.
                return false;
        }

        return $validatedData;
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if ('' === trim($entity->getLabel())) {
            $errorStore->addError('o-module-collecting:label', 'The label cannot be empty.');
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {}
}
