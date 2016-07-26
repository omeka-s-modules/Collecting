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
    protected $item;

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

    public function setItem(CollectingItem $item)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $this->item;
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

