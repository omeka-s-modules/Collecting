<?php
namespace Collecting\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class CollectingForm extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column
     */
    protected $label;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @OneToMany(
     *     targetEntity="CollectingPrompt",
     *     mappedBy="collectingForm",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove", "detach"}
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $collectingPrompts;

    public function getId()
    {
        return $this->id;
    }
}
