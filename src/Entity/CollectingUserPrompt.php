<?php
namespace Collecting\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class CollectingUserPrompt extends AbstractEntity
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
     *     inversedBy="userPrompts"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $form;

    /**
     * @OneToMany(
     *     targetEntity="CollectingUserInput",
     *     mappedBy="userPrompt",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    protected $userInputs;

    /**
     * @Column(type="integer")
     */
    protected $position;

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
     * @Column(type="boolean", nullable=false)
     */
    protected $required = false;

    public static function getInputTypes()
    {
        return [
            'text' => 'Text box (one line)', // @translate
            'textarea' => 'Text box (multiple line)', // @translate
            'select' => 'Select menu', // @translate
        ];
    }

    public function __construct() {
        $this->userInputs = new ArrayCollection;
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

    public function setText($text)
    {
        $this->text = trim($text) ?: null;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setInputType($inputType)
    {
        $this->inputType = trim($inputType) ?: null;
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setSelectOptions($selectOptions)
    {
        $this->selectOptions = trim($selectOptions) ?: null;
    }

    public function getSelectOptions()
    {
        return $this->selectOptions;
    }

    public function setRequired($required)
    {
        $this->required = (bool) $required;
    }

    public function getRequired()
    {
        return $this->required;
    }
}
