<?php

namespace App\Entity;

use App\Repository\TypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @package App\Entity
 */
#[ORM\Entity(repositoryClass: TypeRepository::class)]
#[ORM\Table(name: 'type')]
#[ORM\UniqueConstraint(name: 'type_code_idx', columns: ['code'])]
class Type implements TypeInterface
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
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'type')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    protected $cards;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->cards = new ArrayCollection();
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
    public function addCard(CardInterface $card)
    {
        $this->cards->add($card);
    }

    /**
     * @inheritdoc
     */
    public function removeCard(CardInterface $card)
    {
        $this->cards->removeElement($card);
    }

    /**
     * @inheritdoc
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return [
            'code' => $this->code,
            'name' => $this->name
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
     * @return string
     */
    public function __toString()
    {
        return $this->name ?: '';
    }
}
