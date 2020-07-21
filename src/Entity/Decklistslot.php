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
     * @param Decklist $decklist
     */
    public function setDecklist(Decklist $decklist = null)
    {
        $this->decklist = $decklist;
    }

    /**
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
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
