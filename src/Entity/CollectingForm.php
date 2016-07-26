<?php
namespace Collecting\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\ItemSet;
use Omeka\Entity\Site;
use Omeka\Entity\User;

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
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\ItemSet"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $itemSet;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\Site"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $site;

    /**
     * @ManyToOne(
     *     targetEntity="Omeka\Entity\User"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $owner;

    /**
     * @OneToMany(
     *     targetEntity="CollectingPrompt",
     *     mappedBy="form",
     *     indexBy="id",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $prompts;

    /**
     * @OneToMany(
     *     targetEntity="CollectingUserPrompt",
     *     mappedBy="form",
     *     indexBy="id",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     * @OrderBy({"position" = "ASC"})
     */
    protected $userPrompts;

    public function __construct() {
        $this->prompts = new ArrayCollection;
        $this->userPrompts = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setLabel($label)
    {
        $this->label = $label;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setDescription($description)
    {
        $this->description = trim($description) ?: null;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setItemSet(ItemSet $itemSet = null)
    {
        $this->itemSet = $itemSet;
    }

    public function getItemSet()
    {
        return $this->itemSet;
    }

    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    public function setSite(Site $site = null)
    {
        $this->site = $site;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function getPrompts()
    {
        return $this->prompts;
    }

    public function getUserPrompts()
    {
        return $this->prompts;
    }
}
