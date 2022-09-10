<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Interface RestrictionInterface
 * @package App\Entity

 * @ORM\Table(name="restriction")
 * @ORM\Entity(repositoryClass="App\Repository\RestrictionRepository")
 */
class Restriction implements RestrictionInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=20)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @Assert\Type(type="string")
     */
    protected string $code;

    /**
     * @ORM\Column(name="effective_on", type="datetime")
     *
     * @Assert\NotBlank()
     */
    protected DateTime $effectiveOn;

    /**
     * @ORM\Column(name="title", type="string", length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 50
     * )
     */
    protected string $title;

    /**
     * @ORM\Column(name="issuer", type="string", length=50)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 50
     * )
     */
    protected string $issuer;

    /**
     * @ORM\Column(name="card_set", type="string", length=20)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 20
     * )
     */
    protected string $cardSet;

    /**
     * @ORM\Column(name="contents", type="json")
     *
     * @Assert\Type(type="array")
     */
    protected array $contents;

    /**
     * @ORM\Column(name="active", type="boolean")
     *
     * @Assert\NotNull()
     * @Assert\Type(type="bool")
     */
    protected bool $active;

    /**
     * @ORM\Column(name="version", type="string", length=20)
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     * @Assert\Length(
     *      min = 1,
     *      max = 20
     * )
     */
    protected string $version;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="date_update", type="datetime", nullable=false)
     */
    protected DateTime $dateUpdate;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected DateTime $dateCreation;

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
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setEffectiveOn(DateTime $effectiveOn): void
    {
        $this->effectiveOn = $effectiveOn;
    }
    /**
     * @inheritdoc
     */
    public function getEffectiveOn(): DateTime
    {
        return $this->effectiveOn;
    }

    /**
     * @inheritdoc
     */
    public function setIssuer(string $issuer): void
    {
        $this->issuer = $issuer;
    }

    /**
     * @inheritdoc
     */
    public function getIssuer(): string
    {
        return $this->issuer;
    }

    /**
     * @inheritdoc
     */
    public function setCardSet(string $cardSet): void
    {
        $this->cardSet = $cardSet;
    }

    /**
     * @inheritdoc
     */
    public function getCardSet(): string
    {
        return $this->cardSet;
    }

    /**
     * @inheritdoc
     */
    public function setContents(array $contents): void
    {
        $this->contents = $contents;
    }

    /**
     * @inheritdoc
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    /**
     * @inheritdoc
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @inheritdoc
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @inheritdoc
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * @inheritdoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function setDateUpdate(DateTime $dateUpdate): void
    {
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getDateUpdate(): DateTime
    {
        return $this->dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreation(DateTime $dateCreation): void
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreation(): DateTime
    {
        return $this->dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function getJoustRestrictedList(): array
    {
        return $this->getContents()['joust']['restricted'];
    }

    /**
     * @inheritdoc
     */
    public function getJoustUrl(): ?string
    {
        return $this->getContents()['joust']['url'];
    }

    /**
     * @inheritdoc
     */
    public function getJoustBannedList(): array
    {
        return $this->getContents()['joust']['banned'];
    }

    /**
     * @inheritdoc
     */
    public function getJoustRestrictedPods(): array
    {
        return $this->getContents()['joust']['restricted_pods'];
    }

    /**
     * @inheritdoc
     */
    public function getMeleeRestrictedList(): array
    {
        return $this->getContents()['melee']['restricted'];
    }

    /**
     * @inheritdoc
     */
    public function getMeleeUrl(): ?string
    {
        return $this->getContents()['melee']['url'];
    }

    /**
     * @inheritdoc
     */
    public function getMeleeBannedList(): array
    {
        return $this->getContents()['melee']['banned'];
    }

    /**
     * @inheritdoc
     */
    public function getMeleeRestrictedPods(): array
    {
        return $this->getContents()['melee']['restricted_pods'];
    }

    /**
     * @inheritdoc
     */
    public function getReferencedCards(): array
    {
        $cardsInPods = array_map(function (array $pod) {
            $rhett = [];
            if (array_key_exists('restricted', $pod) && $pod['restricted']) {
                $rhett[] = $pod['restricted'];
            }
            return array_merge($rhett, $pod['cards']);
        },
        array_merge($this->getJoustRestrictedPods(), $this->getMeleeRestrictedPods()));
        return array_unique(
            array_merge(
                $this->getJoustRestrictedList(),
                $this->getJoustBannedList(),
                $this->getMeleeRestrictedList(),
                $this->getMeleeBannedList(),
                ...$cardsInPods
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->getCode(),
            'title' => $this->getTitle(),
            'effectiveOn' => $this->getEffectiveOn()->format('c'),
            'issuer' => $this->getIssuer(),
            'cardSet' => $this->getCardSet(),
            'contents' => $this->getContents(),
            'active' => $this->isActive(),
            'version' => $this->getVersion(),
            'dateUpdate' => $this->getDateUpdate()->format('c'),
            'dateCreation' => $this->getDateCreation()->format('c'),
        ];
    }
}
