<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Exception;

/**
 * Class Card
 * @package App\Entity
 * @ORM\Table(
 *     name="card",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="card_code_idx", columns={"code"})},
 *     indexes={@ORM\Index(name="card_name_idx", columns={"name"})}
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CardRepository")
 */
class Card implements CardInterface
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
     * @var int
     *
     * @ORM\Column(name="position", type="smallint", nullable=false)
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    protected $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="cost", type="string", length=2, nullable=true)
     */
    protected $cost;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    protected $text;

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
     * @var int
     *
     * @ORM\Column(name="quantity", type="smallint", nullable=false)
     */
    protected $quantity;

    /**
     * @var int|null
     *
     * @ORM\Column(name="income", type="smallint", nullable=true)
     */
    protected $income;

    /**
     * @var int|null
     *
     * @ORM\Column(name="initiative", type="smallint", nullable=true)
     */
    protected $initiative;

    /**
     * @var int|null
     *
     * @ORM\Column(name="claim", type="smallint", nullable=true)
     */
    protected $claim;

    /**
     * @var int|null
     *
     * @ORM\Column(name="reserve", type="smallint", nullable=true)
     */
    protected $reserve;

    /**
     * @var int|null
     *
     * @ORM\Column(name="deck_limit", type="smallint", nullable=true)
     */
    protected $deckLimit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="designer", type="text", length=255, nullable=true)
     */
    protected $designer;

    /**
     * @var int|null
     *
     * @ORM\Column(name="strength", type="smallint", nullable=true)
     */
    protected $strength;

    /**
     * @var string
     *
     * @ORM\Column(name="traits", type="string", length=255, nullable=false)
     */
    protected $traits;

    /**
     * @var string
     *
     * @ORM\Column(name="flavor", type="text", nullable=false)
     */
    protected $flavor;

    /**
     * @var string|null
     *
     * @ORM\Column(name="illustrator", type="string", length=255, nullable=true)
     */
    protected $illustrator;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_unique", type="boolean", nullable=false)
     */
    protected $isUnique;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_loyal", type="boolean", nullable=false)
     */
    protected $isLoyal;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_military", type="boolean", nullable=false)
     */
    protected $isMilitary;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_intrigue", type="boolean", nullable=false)
     */
    protected $isIntrigue;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_power", type="boolean", nullable=false)
     */
    protected $isPower;

    /**
     * @var string|null
     *
     * @ORM\Column(name="octgn_id", type="string", nullable=true)
     */
    protected $octgnId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_multiple", type="boolean", nullable=false)
     */
    protected $isMultiple;

    /**
     * @var string|null
     *
     * @ORM\Column(name="image_url", type="string", length=255, nullable=true)
     */
    protected $imageUrl;

    /**
     * @var bool
     *
     * @ORM\Column(name="errataed", type="boolean", nullable=false)
     */
    protected bool $errataed;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="card")
     * @ORM\OrderBy({
     *     "dateCreation"="DESC"
     * })
     */
    protected $reviews;

    /**
     * @var RarityInterface|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Rarity", inversedBy="cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="rarity_id", referencedColumnName="id", nullable=true)
     * })
     */
    protected ?RarityInterface $rarity = null;

    /**
     * @var Pack
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pack", inversedBy="cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pack_id", referencedColumnName="id")
     * })
     */
    protected $pack;

    /**
     * @var Type
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Type", inversedBy="cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type_id", referencedColumnName="id")
     * })
     */
    protected $type;

    /**
     * @var FactionInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Faction", inversedBy="cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="faction_id", referencedColumnName="id")
     * })
     */
    protected $faction;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->isMilitary = false;
        $this->isIntrigue = false;
        $this->isPower = false;
        $this->isMultiple = false;
        $this->errataed = false;

        $this->reviews = new ArrayCollection();
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
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @inheritdoc
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @inheritdoc
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @inheritdoc
     */
    public function getText()
    {
        return $this->text;
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
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function setIncome($income)
    {
        $this->income = $income;
    }

    /**
     * @inheritdoc
     */
    public function getIncome()
    {
        return $this->income;
    }

    /**
     * @inheritdoc
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;
    }

    /**
     * @inheritdoc
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * @inheritdoc
     */
    public function setClaim($claim)
    {
        $this->claim = $claim;
    }

    /**
     * @inheritdoc
     */
    public function getClaim()
    {
        return $this->claim;
    }

    /**
     * @inheritdoc
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;
    }

    /**
     * @inheritdoc
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * @inheritdoc
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;
    }

    /**
     * @inheritdoc
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * @inheritdoc
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
    }

    /**
     * @inheritdoc
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * @inheritdoc
     */
    public function setTraits($traits)
    {
        $this->traits = $traits;
    }

    /**
     * @inheritdoc
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @inheritdoc
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;
    }

    /**
     * @inheritdoc
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @inheritdoc
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;
    }

    /**
     * @inheritdoc
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * @inheritdoc
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;
    }

    /**
     * @inheritdoc
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * @inheritdoc
     */
    public function setIsLoyal($isLoyal)
    {
        $this->isLoyal = $isLoyal;
    }

    /**
     * @inheritdoc
     */
    public function getIsLoyal()
    {
        return $this->isLoyal;
    }

    /**
     * @inheritdoc
     */
    public function setIsMilitary($isMilitary)
    {
        $this->isMilitary = $isMilitary;
    }

    /**
     * @inheritdoc
     */
    public function getIsMilitary()
    {
        return $this->isMilitary;
    }

    /**
     * @inheritdoc
     */
    public function setIsIntrigue($isIntrigue)
    {
        $this->isIntrigue = $isIntrigue;
    }

    /**
     * @inheritdoc
     */
    public function getIsIntrigue()
    {
        return $this->isIntrigue;
    }

    /**
     * @inheritdoc
     */
    public function setIsPower($isPower)
    {
        $this->isPower = $isPower;
    }

    /**
     * @inheritdoc
     */
    public function getIsPower()
    {
        return $this->isPower;
    }

    /**
     * @inheritdoc
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;
    }

    /**
     * @inheritdoc
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    public function setErrataed(bool $errataed): void
    {
        $this->errataed = $errataed;
    }

    public function getErrataed(): bool
    {
        return $this->errataed;
    }

    /**
     * @inheritdoc
     */
    public function addReview(ReviewInterface $review)
    {
        $this->reviews->add($review);
    }

    /**
     * @inheritdoc
     */
    public function removeReview(ReviewInterface $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * @inheritdoc
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @inheritdoc
     */
    public function getRarity(): ?RarityInterface
    {
        return $this->rarity;
    }

    /**
     * @inheritdoc
     */
    public function setRarity(?RarityInterface $rarity): void
    {
        $this->rarity = $rarity;
    }

    /**
     * @inheritdoc
     */
    public function setPack(PackInterface $pack = null)
    {
        $this->pack = $pack;
    }

    /**
     * @inheritdoc
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * @inheritdoc
     */
    public function setType(TypeInterface $type = null)
    {
        $this->type = $type;
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setFaction(FactionInterface $faction = null)
    {
        $this->faction = $faction;
    }

    /**
     * @inheritdoc
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @inheritdoc
     */
    public function getCostIncome()
    {
        $cost = $this->getCost();
        $income = $this->getIncome();

        if (is_null($income) && is_null($cost)) {
            return "";
        }

        return $cost ?? (string)$income;
    }

    /**
     * @inheritdoc
     */
    public function getStrengthInitiative()
    {
        $strength = $this->getStrength();
        $initiative = $this->getInitiative();

        if (is_null($strength) and is_null($initiative)) {
            return -1;
        }

        return max($strength, $initiative);
    }

    /**
     * @inheritdoc
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;
    }

    /**
     * @inheritdoc
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * @inheritdoc
     */
    public function getIsMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * @inheritdoc
     */
    public function setIsMultiple(bool $isMultiple)
    {
        $this->isMultiple = $isMultiple;
    }

    /**
     * @inheritdoc
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @inheritdoc
     */
    public function setImageUrl(string $imageUrl = null)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * @inheritdoc
     */
    public function hasShadowKeyword($shadow): bool
    {
        // "Shadow (<cost>).", with <cost> being either digits or the letter "X"
        $regex = "/${shadow} \\(([0-9]+|X)\\)\\./";
        // check if first line in the card text has that keyword.
        $textLines = explode("\n", $this->getText());

        return preg_match($regex, $textLines[0]) ? true : false;
    }

    /**
     * @inheritdoc
     */
    public function serialize()
    {
        $serialized = [];
        if (empty($this->code)) {
            return $serialized;
        }

        $mandatoryFields = [
            'code',
            'deck_limit',
            'position',
            'quantity',
            'name',
            'traits',
            'is_loyal',
            'is_unique',
            'is_multiple',
            'octgn_id',
            'errataed',
        ];

        $optionalFields = [
            'illustrator',
            'flavor',
            'text',
            'cost',
        ];

        $externalFields = [
            'faction',
            'pack',
            'type',
            'rarity',
        ];

        switch ($this->type->getCode()) {
            case 'agenda':
            case 'title':
                break;
            case 'attachment':
            case 'event':
            case 'location':
                $mandatoryFields[] = 'cost';
                break;
            case 'character':
                $mandatoryFields[] = 'cost';
                $mandatoryFields[] = 'strength';
                $mandatoryFields[] = 'is_military';
                $mandatoryFields[] = 'is_intrigue';
                $mandatoryFields[] = 'is_power';
                break;
            case 'plot':
                $mandatoryFields[] = 'claim';
                $mandatoryFields[] = 'income';
                $mandatoryFields[] = 'initiative';
                $mandatoryFields[] = 'reserve';
                break;
        }

        foreach ($optionalFields as $optionalField) {
            $getter = 'get' . $this->snakeToCamel($optionalField);
            $serialized[$optionalField] = $this->$getter();
            if (!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') {
                unset($serialized[$optionalField]);
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            $getter = 'get' . $this->snakeToCamel($mandatoryField);
            $serialized[$mandatoryField] = $this->$getter();
        }

        foreach ($externalFields as $externalField) {
            $getter = 'get' . $this->snakeToCamel($externalField);
            $externalEntity = $this->$getter();
            $serialized[$externalField . '_code'] = $externalEntity ? $externalEntity->getCode() : null;
        }

        ksort($serialized);

        return $serialized;
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

    /**
     * Converts a given snake_cased text to CamelCase.
     * @param string $snake
     * @return string
     */
    protected function snakeToCamel($snake)
    {
        $parts = explode('_', $snake);

        return implode('', array_map('ucfirst', $parts));
    }
}
