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
     * @Column
     */
    protected $anonType;

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
     *     nullable=false,
     *     onDelete="CASCADE"
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

    public static function getAnonTypes()
    {
        return [
            'user' => 'User sets own anonymity', // @translate
            'public' => 'All "User Public" inputs are publicly visible', // @translate
            'private' => 'All "User Public" inputs are private', // @translate
        ];
    }

    public function __construct() {
        $this->prompts = new ArrayCollection;
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

    public function setAnonType($anonType)
    {
        $this->anonType = $anonType;
    }

    public function getAnonType()
    {
        return $this->anonType;
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
}
