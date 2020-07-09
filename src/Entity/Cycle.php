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
 * Cycle
 *
 * @ORM\Table(name="cycle", uniqueConstraints={@ORM\UniqueConstraint(name="cycle_code_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="App\Repository\CycleRepository")
 */

class Cycle implements Serializable
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Pack", mappedBy="cycle")
     * @ORM\OrderBy({
     *     "position"="ASC"
     * })
     */
    protected $packs;

    /**
     * Cycle constructor.
     */
    public function __construct()
    {
        $this->packs = new ArrayCollection();
    }

    /**
     * @param $id
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
     * @param Pack $pack
     */
    public function addPack(Pack $pack)
    {
        $this->packs->add($pack);
    }

    /**
     * @param Pack $pack
     */
    public function removePack(Pack $pack)
    {
        $this->packs->removeElement($pack);
    }

    /**
     * @return Collection
     */
    public function getPacks()
    {
        return $this->packs;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'position' => $this->position,
            'size' => $this->size
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
