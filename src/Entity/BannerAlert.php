<?php

namespace App\Entity;

use DateTime;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Banner alert class.
 *
 * @package App\Entity
 * @ORM\Table(name="banneralert")
 * @ORM\Entity(repositoryClass="App\Repository\BannerAlertRepository")
 */
class BannerAlert implements BannerAlertInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Assert\Type(type="int")
     */
    protected int $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     * min = 1,
     * max = 255
     * )
     */
    protected string $name;

    /**
     * @ORM\Column(name="text", type="text", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     * min = 1,
     * max = 65000
     * )
     */
    protected string $description;

    /**
     * @ORM\Column(name="level", type="smallint", nullable=false)
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     * @Assert\Range(min=1, max=2)
     */
    protected int $level;

    /**
     * @ORM\Column(name="active", type="boolean")
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    protected bool $active;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected DateTime $dateCreation;


    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setLevel(int $level): void
    {
        $this->level = $level;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setDateCreation(DateTime $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }
}
