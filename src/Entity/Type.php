<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Serializable;

/**
 * Type
 *
 * @ORM\Table(name="type", uniqueConstraints={@ORM\UniqueConstraint(name="type_code_idx", columns={"code"})})
 * @ORM\Entity(repositoryClass="App\Repository\TypeRepository")
 */
class Type implements Serializable
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Card", mappedBy="type")
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
