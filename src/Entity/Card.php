<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Exception;
use Serializable;

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
class Card implements Serializable
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
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="card")
     * @ORM\OrderBy({
     *     "dateCreation"="DESC"
     * })
     */
    protected $reviews;

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
     * @var Faction
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

        $this->reviews = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $cost
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
    }

    /**
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $income
     */
    public function setIncome($income)
    {
        $this->income = $income;
    }

    /**
     * @return int
     */
    public function getIncome()
    {
        return $this->income;
    }

    /**
     * @param int $initiative
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;
    }

    /**
     * @return int
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * @param int $claim
     */
    public function setClaim($claim)
    {
        $this->claim = $claim;
    }

    /**
     * @return int
     */
    public function getClaim()
    {
        return $this->claim;
    }

    /**
     * @param int $reserve
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;
    }

    /**
     * @return int
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * @param int $deckLimit
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;
    }

    /**
     * @return int
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * @param int $strength
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;
    }

    /**
     * @return int
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * @param string $traits
     */
    public function setTraits($traits)
    {
        $this->traits = $traits;
    }

    /**
     * @return string
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;
    }

    /**
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * @param string $illustrator
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;
    }

    /**
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * @param bool $isUnique
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;
    }

    /**
     * @return bool
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * @param bool $isLoyal
     */
    public function setIsLoyal($isLoyal)
    {
        $this->isLoyal = $isLoyal;
    }

    /**
     * @return bool
     */
    public function getIsLoyal()
    {
        return $this->isLoyal;
    }

    /**
     * @param bool $isMilitary
     */
    public function setIsMilitary($isMilitary)
    {
        $this->isMilitary = $isMilitary;
    }

    /**
     * @return bool
     */
    public function getIsMilitary()
    {
        return $this->isMilitary;
    }

    /**
     * @param bool $isIntrigue
     */
    public function setIsIntrigue($isIntrigue)
    {
        $this->isIntrigue = $isIntrigue;
    }

    /**
     * @return bool
     */
    public function getIsIntrigue()
    {
        return $this->isIntrigue;
    }

    /**
     * @param bool $isPower
     */
    public function setIsPower($isPower)
    {
        $this->isPower = $isPower;
    }

    /**
     * @return bool
     */
    public function getIsPower()
    {
        return $this->isPower;
    }

    /**
     * @param bool $octgnId
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;
    }

    /**
     * @return bool
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    /**
     * @param Review $review
     */
    public function addReview(Review $review)
    {
        $this->reviews->add($review);
    }

    /**
     * @param Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param Pack $pack
     */
    public function setPack(Pack $pack = null)
    {
        $this->pack = $pack;
    }

    /**
     * @return Pack
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * @param Type $type
     */
    public function setType(Type $type = null)
    {
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param Faction $faction
     */
    public function setFaction(Faction $faction = null)
    {
        $this->faction = $faction;
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @return string
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
     * @return int
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
     * @param string $designer
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;
    }

    /**
     * @return string
     */
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * @return bool
     */
    public function getIsMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * @param bool $isMultiple
     */
    public function setIsMultiple(bool $isMultiple)
    {
        $this->isMultiple = $isMultiple;
    }

    /**
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @param string $imageUrl
     */
    public function setImageUrl(string $imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Checks if this card has the "Shadow" keyword.
     * @param string $shadow The keyword "Shadow" in whatever language.
     * @return bool
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
            $getter = 'get'.$this->snakeToCamel($optionalField);
            $serialized[$optionalField] = $this->$getter();
            if (!isset($serialized[$optionalField]) || $serialized[$optionalField] === '') {
                unset($serialized[$optionalField]);
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            $getter = 'get'.$this->snakeToCamel($mandatoryField);
            $serialized[$mandatoryField] = $this->$getter();
        }

        foreach ($externalFields as $externalField) {
            $getter = 'get'.$this->snakeToCamel($externalField);
            $serialized[$externalField.'_code'] = $this->$getter()->getCode();
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
