<?php
namespace Collecting\View\Helper;

use Collecting\Entity\CollectingForm;
use Collecting\Entity\CollectingPrompt;
use Collecting\MediaType\Manager;
use Zend\View\Helper\AbstractHelper;

class Collecting extends AbstractHelper
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var array
     */
    protected $inputTypes;

    /**
     * @var array
     */
    protected $mediaTypes;

    /**
     * @var array
     */
    protected $anonTypes;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Get all prompt types.
     *
     * @return array;
     */
    public function types()
    {
        if (null === $this->types) {
            $this->types = CollectingPrompt::getTypes();
        }
        return $this->types;
    }

    /**
     * Get all prompt input types.
     *
     * @return array;
     */
    public function inputTypes()
    {
        if (null === $this->inputTypes) {
            $this->inputTypes = CollectingPrompt::getInputTypes();
        }
        return $this->inputTypes;
    }

    /**
     * Get all prompt media types.
     *
     * @return array;
     */
    public function mediaTypes()
    {
        if (null === $this->mediaTypes) {
            $this->mediaTypes = [];
            $names = $this->manager->getRegisteredNames();
            foreach ($names as $name) {
                $this->mediaTypes[$name] = $this->manager->get($name)->getLabel();
            }
        }
        return $this->mediaTypes;
    }

    /**
     * Get all form anon types.
     *
     * @return array;
     */
    public function anonTypes()
    {
        if (null === $this->anonTypes) {
            $this->anonTypes = CollectingForm::getAnonTypes();
        }
        return $this->anonTypes;
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
        return $this->manager->get($key)->getLabel();
    }

    public function anonTypeValue($key)
    {
        return isset($this->anonTypes()[$key]) ? $this->anonTypes()[$key] : null;
    }
}
