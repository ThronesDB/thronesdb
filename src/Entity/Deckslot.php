<?php

namespace App\Entity;

use App\Model\SlotInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Deckslot
 *
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
     * @var Deck
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deck_id", referencedColumnName="id")
     * })
     */
    protected $deck;

    /**
     * @var Card
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
     * @param Deck $deck
     */
    public function setDeck(Deck $deck = null)
    {
        $this->deck = $deck;
    }

    /**
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param Card $card
     */
    public function setCard(Card $card = null)
    {
        $this->card = $card;
    }

    /**
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }
}
