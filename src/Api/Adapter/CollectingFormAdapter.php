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
                $promptData = $this->validatePromptData($promptData, $errorStore);
                if ($entity->getPrompts()->containsKey($promptData['o:id'])) {
                    // Update an existing prompt.
                    $prompt = $entity->getPrompts()->get($promptData['o:id']);
                } else {
                    // Create a new prompt. Note that the owning form and the
                    // prompt type can only be set when creating a new prompt.
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
     * @param array $data
     * @param ErrorStore $errorStore
     * @return bool Returns false if the prompt data does not validate.
     */
    protected function validatePromptData(array $data, ErrorStore $errorStore)
    {
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
        if (isset($data['o-module-collecting:select_options']) && '' !== trim($data['o-module-collecting:select_options'])) {
            $validatedData['o-module-collecting:select_options'] = $this->sanitizeSelectOptions($data['o-module-collecting:select_options']);
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
                    $errorStore->addError('o:property', 'A property prompt must have a property.');
                }
                if (null === $validatedData['o-module-collecting:input_type']) {
                    $errorStore->addError('o-module-collecting:input_type', 'A property prompt must have an input type.');
                }
                break;
            case 'media':
                if (null === $validatedData['o-module-collecting:media_type']) {
                    $errorStore->addError('o-module-collecting:media_type', 'A media prompt must have a media type.');
                }
                break;
            case 'input':
                if (null === $validatedData['o-module-collecting:text']) {
                    $errorStore->addError('o-module-collecting:text', 'An input prompt must have text.');
                }
                if (null === $validatedData['o-module-collecting:input_type']) {
                    $errorStore->addError('o-module-collecting:input_type', 'An input prompt must have an input type.');
                }
                break;
            default:
                $errorStore->addError('o-module-collecting:type', 'Prompts must have a type.');
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
    {
        if (isset($query['site_id'])) {
            $qb->andWhere($qb->expr()->eq(
                $this->getEntityClass() . '.site',
                $this->createNamedParameter($qb, $query['site_id']))
            );
        }
    }

    /**
     * Sanitize the select options for insertion into the database.
     *
     * @param string $terms
     * @return string
     */
    protected function sanitizeSelectOptions($options)
    {
        $options = explode("\n", $options);
        $options = array_map('trim', $options); // trim all values
        $options = array_filter($options); // remove empty values
        $options = array_unique($options); // remove duplicate values
        return trim(implode("\n", $options));
    }
}
