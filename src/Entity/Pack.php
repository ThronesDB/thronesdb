<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Serializable;

/**
 * Pack
 *
 * @ORM\Table(name="pack", uniqueConstraints={@ORM\UniqueConstraint(name="pack_code_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="App\Repository\PackRepository")
 */
class Pack implements Serializable
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
     * @var int
     *
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="smallint", nullable=false)
     */
    protected $size;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected $dateCreation;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="date_update", type="datetime", nullable=false)
     */
    protected $dateUpdate;

    /**
     * @var DateTime|null
     *
     * @ORM\Column(name="date_release", type="date", nullable=true)
     */
    protected $dateRelease;

    /**
     * @var int|null
     *
     * @ORM\Column(name="cgdb_id", type="integer", nullable=true)
     */
    protected $cgdbId;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="pack")
     * @ORM\OrderBy({
     *     "position"="ASC"
     * })
     */
    protected $cards;

    /**
     * @var Cycle
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Cycle", inversedBy="packs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cycle_id", referencedColumnName="id")
     * })
     */
    protected $cycle;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    /**
     * @param int $id
     * @return Pack
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
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
     * @return Pack
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
     * @return Pack
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
     * Set position
     *
     * @param int $position
     *
     * @return Pack
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set size
     *
     * @param int $size
     *
     * @return Pack
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set dateCreation
     *
     * @param DateTime $dateCreation
     *
     * @return Pack
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param DateTime $dateUpdate
     *
     * @return Pack
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set dateRelease
     *
     * @param DateTime $dateRelease
     *
     * @return Pack
     */
    public function setDateRelease($dateRelease)
    {
        $this->dateRelease = $dateRelease;

        return $this;
    }

    /**
     * Get dateRelease
     *
     * @return DateTime
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * Set cgdbId
     *
     * @param int $cgdbId
     *
     * @return Pack
     */
    public function setCgdbId($cgdbId)
    {
        $this->cgdbId = $cgdbId;

        return $this;
    }

    /**
     * Get cgdbId
     *
     * @return int
     */
    public function getCgdbId()
    {
        return $this->cgdbId;
    }

    /**
     * Add card
     *
     * @param \App\Entity\Card $card
     *
     * @return Pack
     */
    public function addCard(\App\Entity\Card $card)
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * Remove card
     *
     * @param \App\Entity\Card $card
     */
    public function removeCard(\App\Entity\Card $card)
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

    /**
     * Set cycle
     *
     * @param Cycle $cycle
     *
     * @return Pack
     */
    public function setCycle(Cycle $cycle = null)
    {
        $this->cycle = $cycle;

        return $this;
    }

    /**
     * Get cycle
     *
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    public function serialize()
    {
        return [
            'code' => $this->code,
            'cycle_code' => $this->cycle ? $this->cycle->getCode() : null,
            'date_release' => $this->dateRelease ? $this->dateRelease->format('Y-m-d') : null,
            'name' => $this->name,
            'position' => $this->position,
            'size' => $this->size,
            'cgdb_id' => $this->cgdbId
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
