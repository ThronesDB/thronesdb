<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Doctrine\ORM\Mapping as ORM;

/**
 * @package App\Entity
 */
#[ORM\Entity]
#[ORM\Table(name: 'rarity')]
#[ORM\UniqueConstraint(name: 'rarity_code_idx', columns: ['code'])]
class Rarity implements RarityInterface
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected int $id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'code', type: 'string', length: 255, nullable: false)]
    protected string $code;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255, nullable: false)]
    protected string $name;

    /**
     * @var Collection
     */
    #[ORM\OneToMany(targetEntity: Card::class, mappedBy: 'rarity')]
    #[ORM\OrderBy(['id' => 'ASC'])]
    protected Collection $cards;


    public function __construct()
    {
        $this->cards = new ArrayCollection();
    }

    /**
     * @inheritdoc
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @inheritdoc
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @inheritdoc
     */
    public function addCard(CardInterface $card): void
    {
        $this->cards->add($card);
    }

    /**
     * @inheritdoc
     */
    public function removeCard(CardInterface $card): void
    {
        $this->cards->removeElement($card);
    }

    /**
     * @inheritdoc
     */
    public function getCards(): Collection
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
    public function unserialize($data): void
    {
        throw new Exception("unserialize() method unsupported");
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code ?: '';
    }
}
