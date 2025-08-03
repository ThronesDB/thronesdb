<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="decklistslot")
 * @ORM\Entity
 * @package App\Entity
 */
class Decklistslot implements DecklistslotInterface
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
    public function setDecklist(Decklist $decklist = null)
    {
        $this->decklist = $decklist;
    }

    /**
     * @inheritdoc
     */
    public function getDecklist()
    {
        return $this->decklist;
    }

    /**
     * @inheritdoc
     */
    public function setCard(CardInterface $card = null)
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
