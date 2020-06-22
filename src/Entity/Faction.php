<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Serializable;
use Doctrine\ORM\Mapping as ORM;

/**
 * Faction
 *
 * @ORM\Table(name="faction", uniqueConstraints={@ORM\UniqueConstraint(name="faction_code_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="App\Repository\FactionRepository")
 */
class Faction implements Serializable
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
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=1024, nullable=false)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=false)
     */
    protected $isPrimary;

    /**
     * @var string|null
     *
     * @ORM\Column(name="octgn_id", type="string", nullable=true)
     */
    protected $octgnId;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="faction")
     * @ORM\OrderBy({
     *     "position"="ASC"
     * })
     */
    protected $cards;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    /**
     * Set id
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
     * Set code
     *
     * @param string $code
     *
     * @return Faction
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Faction
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isPrimary
     *
     * @param bool $isPrimary
     *
     * @return Faction
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * Get isPrimary
     *
     * @return bool
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * Set octgnId
     *
     * @param string $octgnId
     *
     * @return Faction
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;

        return $this;
    }

    /**
     * Get octgnId
     *
     * @return string
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    /**
     * Add card
     *
     * @param Card $card
     *
     * @return Faction
     */
    public function addCard(Card $card)
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * Remove card
     *
     * @param Card $card
     */
    public function removeCard(Card $card)
    {
        $this->cards->removeElement($card);
    }

    /**
     * Get cards
     *
     * @return Collection
     */
    public function getCards()
    {
        return $this->cards;
    }

    public function serialize()
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'is_primary' => $this->isPrimary,
            'octgn_id' => $this->octgnId
        ];
    }

    /**
     * @param string $serialized
     * @throws Exception
     */
    public function unserialize($serialized)
    {
        throw new Exception("unserialize() method unsupported");
    }

    public function __toString()
    {
        return $this->name ?: '';
    }
}
