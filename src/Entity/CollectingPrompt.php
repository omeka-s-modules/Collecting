<?php
namespace Collecting\Entity;

use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Property;

/**
 * @Entity
 */
class CollectingPrompt extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingForm",
     *     inversedBy="prompts"
     * )
     * @JoinColumn(nullable=false)
     */
    protected $form;

    /**
     * @OneToMany(
     *     targetEntity="CollectingInput",
     *     mappedBy="prompt",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"}
     * )
     */
    protected $inputs;

    /**
     * @Column(type="integer")
     */
    protected $position;

    /**
     * @Column
     */
    protected $type;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $text;

    /**
     * @Column(nullable=true)
     */
    protected $inputType;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $selectOptions;

    /**
     * @Column(nullable=true)
     */
    protected $mediaType;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Property")
     * @JoinColumn(nullable=true)
     */
    protected $property;

    public static function getTypes()
    {
        return [
            'property' => 'Property', // @translate
            'media' => 'Media', // @translate
            'input' => 'Supplementary', // @translate
        ];
    }

    public static function getInputTypes()
    {
        return [
            'text' => 'Text box (one line)', // @translate
            'textarea' => 'Text box (multiple line)', // @translate
            'select' => 'Select menu', // @translate
        ];
    }

    public static function getMediaTypes()
    {
        return [
            'upload' => 'Upload', // @translate
            'url' => 'URL', // @translate
            'html' => 'HTML', // @translate
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function setForm(CollectingForm $form)
    {
        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setPosition($position)
    {
        $this->position = $position;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setSelectOptions($selectOptions)
    {
        $this->selectOptions = $selectOptions;
    }

    public function getSelectOptions()
    {
        return $this->selectOptions;
    }

    public function setMediaType($mediaType)
    {
        $this->mediaType = $mediaType;
    }

    public function getMediaType()
    {
        return $this->mediaType;
    }

    public function setProperty(Property $property = null)
    {
        $this->property = $property;
    }

    public function getProperty()
    {
        return $this->property;
    }
}
