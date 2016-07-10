<?php
namespace Collecting\Entity;

use Collecting\Entity\CollectingItem;
use Collecting\Entity\CollectingPrompt;
use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class CollectingInput extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingPrompt",
     *     inversedBy="inputs"
     * )
     * @JoinColumn(
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $prompt;

    /**
     * @ManyToOne(
     *     targetEntity="CollectingItem",
     *     inversedBy="inputs"
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

    public function setPrompt(CollectingPrompt $prompt)
    {
        $this->prompt = $prompt;
    }

    public function getPrompt()
    {
        return $this->prompt;
    }

    public function setItem(CollectingItem $item)
    {
        $this->item = $item;
    }

    public function getItem()
    {
        return $this->item;
    }
}
