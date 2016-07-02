<?php
namespace Collecting\Entity;

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
     * @JoinColumn(nullable=false)
     */
    protected $prompt;

    /**
     * @Column(type="text")
     */
    protected $text;

    public function getId()
    {
        return $this->id;
    }
}
