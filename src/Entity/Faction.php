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
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param bool $isPrimary
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;
    }

    /**
     * @return bool
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @param string $octgnId
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;
    }

    /**
     * @return string
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    /**
     * @param CardInterface $card
     */
    public function addCard(CardInterface $card)
    {
        $this->cards->add($card);
    }

    /**
     * @param CardInterface $card
     */
    public function removeCard(CardInterface $card)
    {
        $this->cards->removeElement($card);
    }

    /**
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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name ?: '';
    }
}
