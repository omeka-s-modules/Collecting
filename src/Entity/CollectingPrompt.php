<?php
namespace Collecting\Entity;

use Omeka\Entity\AbstractEntity;

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
     *     inversedBy="collectingPrompts"
     * )
     * @JoinColumn(nullable=false)
     */
    protected $collectingForm;

    /**
     * @ManyToOne(targetEntity="Omeka\Entity\Property")
     * @JoinColumn(nullable=true)
     */
    protected $property;

    /**
     * @OneToMany(
     *     targetEntity="CollectingInput",
     *     mappedBy="collectingPrompt",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"}
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $collectingInputs;

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
    protected $mediaType;

    /**
     * @Column(nullable=true)
     */
    protected $inputType;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $selectOptions;

    public function getId()
    {
        return $this->id;
    }
}
