<?php

namespace App\Entity;

use App\Model\SlotInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Decklistslot
 *
 * @ORM\Table(name="decklistslot")
 * @ORM\Entity
 */
class Decklistslot implements SlotInterface
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
     * @var Decklist
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Decklist", inversedBy="slots")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="decklist_id", referencedColumnName="id")
     * })
     */
    protected $decklist;

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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return Decklistslot
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set decklist
     *
     * @param Decklist $decklist
     *
     * @return Decklistslot
     */
    public function setDecklist(Decklist $decklist = null)
    {
        $this->decklist = $decklist;

        return $this;
    }

    /**
     * Get decklist
     *
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * Set card
     *
     * @param Card $card
     *
     * @return Decklistslot
     */
    public function setCard(Card $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }
}
