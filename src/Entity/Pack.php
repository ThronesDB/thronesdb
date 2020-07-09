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
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param DateTime $dateRelease
     */
    public function setDateRelease($dateRelease)
    {
        $this->dateRelease = $dateRelease;
    }

    /**
     * @return DateTime
     */
    public function getDateRelease()
    {
        return $this->dateRelease;
    }

    /**
     * @param int $cgdbId
     */
    public function setCgdbId($cgdbId)
    {
        $this->cgdbId = $cgdbId;
    }

    /**
     * @return int
     */
    public function getCgdbId()
    {
        return $this->cgdbId;
    }

    /**
     * @param Card $card
     */
    public function addCard(Card $card)
    {
        $this->cards->add($card);
    }

    /**
     * @param Card $card
     */
    public function removeCard(Card $card)
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

    /**
     * @param Cycle $cycle
     */
    public function setCycle(Cycle $cycle = null)
    {
        $this->cycle = $cycle;
    }

    /**
     * @return Cycle
     */
    public function getCycle()
    {
        return $this->cycle;
    }

    /**
     * @inheritdoc
     */
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
     * @inheritdoc
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
