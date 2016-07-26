<?php
namespace Collecting\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class CollectingUserInput extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingUserPrompt",
     *     inversedBy="userInputs"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $userPrompt;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingItem",
     *     inversedBy="userInputs"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $collectingItem;

    /**
     * @Column(type="text")
     */
    protected $text;

    public function getId()
    {
        return $this->id;
    }

    public function setUserPrompt(CollectingUserPrompt $userPrompt)
    {
        $this->userPrompt = $userPrompt;
    }

    public function getUserPrompt()
    {
        return $this->userPrompt;
    }

    public function setCollectingItem(CollectingItem $collectingItem)
    {
        $this->collectingItem = $collectingItem;
    }

    public function getCollectingItem()
    {
        return $this->collectingItem;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
}

