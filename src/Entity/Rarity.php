<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="rarity", uniqueConstraints={@ORM\UniqueConstraint(name="rarity_code_idx", columns={"code"})})
 * @ORM\Entity()
 * @package App\Entity
 */
class Rarity implements RarityInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected int $id;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    protected string $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected string $name;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="rarity")
     * @ORM\OrderBy({
     *     "id"="ASC"
     * })
     */
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
