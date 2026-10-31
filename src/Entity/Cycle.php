<?php

namespace App\Entity;

use App\Repository\CycleRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CycleRepository::class)]
#[ORM\Table(name: 'cycle')]
#[ORM\UniqueConstraint(name: 'cycle_code_idx', columns: ['code'])]
class Cycle implements CycleInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'string', length: 255, nullable: false)]
    protected $code;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 1024, nullable: false)]
    protected $name;

    /**
     * @var int
     */
    #[ORM\Column(name: 'position', type: 'smallint', nullable: false)]
    protected $position;

    /**
     * @var int
     */
    #[ORM\Column(name: 'size', type: 'smallint', nullable: false)]
    protected $size;

    /**
     * @var DateTime
     */
    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'date_creation', type: 'datetime', nullable: false)]
    protected $dateCreation;

    /**
     * @var DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'date_update', type: 'datetime', nullable: false)]
    protected $dateUpdate;

    /**
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: Pack::class, mappedBy: 'cycle')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $packs;

    public function __construct()
    {
        $this->packs = new ArrayCollection();
    }

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
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function addPack(PackInterface $pack)
    {
        $this->packs->add($pack);
    }

    /**
     * @inheritdoc
     */
    public function removePack(PackInterface $pack)
    {
        $this->packs->removeElement($pack);
    }

    /**
     * @inheritdoc
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
     * PHP 8.1+ uses these instead of the Serializable methods.
     * @return array
     */
    public function __serialize(): array
    {
        return $this->serialize();
    }

    public function __unserialize(array $data): void
    {
        $this->unserialize($data);
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
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->name ?: '';
    }
}
