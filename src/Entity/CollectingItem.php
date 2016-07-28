<?php
namespace Collecting\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Omeka\Entity\AbstractEntity;
use Omeka\Entity\Item;

/**
 * @Entity
 */
class CollectingItem extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(
     *     targetEntity="Omeka\Entity\Item"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     */
    protected $item;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingForm",
     *     inversedBy="items"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $form;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingUser",
     *     inversedBy="items",
     *     cascade={"persist"}
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $collectingUser;

    /**
     * @OneToMany(
     *     targetEntity="CollectingInput",
     *     mappedBy="item",
     *     orphanRemoval=true,
     *     cascade={"all"}
     * )
     */
    protected $inputs;

    public function __construct() {
        $this->inputs = new ArrayCollection;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setItem(Item $item = null)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setForm(CollectingForm $form)
    {
        $this->form = $form;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setCollectingUser(CollectingUser $collectingUser)
    {
        $this->collectingUser = $collectingUser;
    }

    public function geCollectingUser()
    {
        return $this->collectingUser;
    }

    public function getInputs()
    {
        return $this->inputs;
    }
}
