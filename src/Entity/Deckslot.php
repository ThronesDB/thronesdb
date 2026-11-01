<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package App\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'deckslot')]
class Deckslot implements DeckslotInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var int
     */
    #[ORM\Column(name: 'quantity', type: 'smallint')]
    protected $quantity;

    /**
     * @var DeckInterface
     */
    #[ORM\ManyToOne(targetEntity: Deck::class, inversedBy: 'slots')]
    #[ORM\JoinColumn(name: 'deck_id', referencedColumnName: 'id')]
    protected $deck;

    /**
     * @var CardInterface
     */
    #[ORM\ManyToOne(targetEntity: Card::class)]
    #[ORM\JoinColumn(name: 'card_id', referencedColumnName: 'id')]
    protected $card;

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setDeck(?DeckInterface $deck = null)
    {
        $this->deck = $deck;
    }

    /**
     * @inheritdoc
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @inheritdoc
     */
    public function setCard(?CardInterface $card = null)
    {
        $this->card = $card;
    }

    /**
     * @inheritdoc
     */
    public function getCard()
    {
        return $this->card;
    }
}
