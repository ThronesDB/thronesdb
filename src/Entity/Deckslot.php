<?php

namespace App\Entity;

use App\Model\SlotInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="deckslot")
 * @ORM\Entity
 */
class Deckslot implements SlotInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="smallint")
     */
    protected $quantity;

    /**
     * @var DeckInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deck_id", referencedColumnName="id")
     * })
     */
    protected $deck;

    /**
     * @var CardInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Card")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     * })
     */
    protected $card;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param DeckInterface $deck
     */
    public function setDeck(DeckInterface $deck = null)
    {
        $this->deck = $deck;
    }

    /**
     * @return DeckInterface
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param CardInterface $card
     */
    public function setCard(CardInterface $card = null)
    {
        $this->card = $card;
    }

    /**
     * @return CardInterface
     */
    public function getCard()
    {
        return $this->card;
    }
}
