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
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set position
     *
     * @param int $position
     *
     * @return Card
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return Card
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Card
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cost
     *
     * @param string $cost
     *
     * @return Card
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Card
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set dateCreation
     *
     * @param DateTime $dateCreation
     *
     * @return Card
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set dateUpdate
     *
     * @param DateTime $dateUpdate
     *
     * @return Card
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return Card
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set income
     *
     * @param int $income
     *
     * @return Card
     */
    public function setIncome($income)
    {
        $this->income = $income;

        return $this;
    }

    /**
     * Get income
     *
     * @return int
     */
    public function getIncome()
    {
        return $this->income;
    }

    /**
     * Set initiative
     *
     * @param int $initiative
     *
     * @return Card
     */
    public function setInitiative($initiative)
    {
        $this->initiative = $initiative;

        return $this;
    }

    /**
     * Get initiative
     *
     * @return int
     */
    public function getInitiative()
    {
        return $this->initiative;
    }

    /**
     * Set claim
     *
     * @param int $claim
     *
     * @return Card
     */
    public function setClaim($claim)
    {
        $this->claim = $claim;

        return $this;
    }

    /**
     * Get claim
     *
     * @return int
     */
    public function getClaim()
    {
        return $this->claim;
    }

    /**
     * Set reserve
     *
     * @param int $reserve
     *
     * @return Card
     */
    public function setReserve($reserve)
    {
        $this->reserve = $reserve;

        return $this;
    }

    /**
     * Get reserve
     *
     * @return int
     */
    public function getReserve()
    {
        return $this->reserve;
    }

    /**
     * Set deckLimit
     *
     * @param int $deckLimit
     *
     * @return Card
     */
    public function setDeckLimit($deckLimit)
    {
        $this->deckLimit = $deckLimit;

        return $this;
    }

    /**
     * Get deckLimit
     *
     * @return int
     */
    public function getDeckLimit()
    {
        return $this->deckLimit;
    }

    /**
     * Set strength
     *
     * @param int $strength
     *
     * @return Card
     */
    public function setStrength($strength)
    {
        $this->strength = $strength;

        return $this;
    }

    /**
     * Get strength
     *
     * @return int
     */
    public function getStrength()
    {
        return $this->strength;
    }

    /**
     * Set traits
     *
     * @param string $traits
     *
     * @return Card
     */
    public function setTraits($traits)
    {
        $this->traits = $traits;

        return $this;
    }

    /**
     * Get traits
     *
     * @return string
     */
    public function getTraits()
    {
        return $this->traits;
    }

    /**
     * Set flavor
     *
     * @param string $flavor
     *
     * @return Card
     */
    public function setFlavor($flavor)
    {
        $this->flavor = $flavor;

        return $this;
    }

    /**
     * Get flavor
     *
     * @return string
     */
    public function getFlavor()
    {
        return $this->flavor;
    }

    /**
     * Set illustrator
     *
     * @param string $illustrator
     *
     * @return Card
     */
    public function setIllustrator($illustrator)
    {
        $this->illustrator = $illustrator;

        return $this;
    }

    /**
     * Get illustrator
     *
     * @return string
     */
    public function getIllustrator()
    {
        return $this->illustrator;
    }

    /**
     * Set isUnique
     *
     * @param bool $isUnique
     *
     * @return Card
     */
    public function setIsUnique($isUnique)
    {
        $this->isUnique = $isUnique;

        return $this;
    }

    /**
     * Get isUnique
     *
     * @return bool
     */
    public function getIsUnique()
    {
        return $this->isUnique;
    }

    /**
     * Set isLoyal
     *
     * @param bool $isLoyal
     *
     * @return Card
     */
    public function setIsLoyal($isLoyal)
    {
        $this->isLoyal = $isLoyal;

        return $this;
    }

    /**
     * Get isLoyal
     *
     * @return bool
     */
    public function getIsLoyal()
    {
        return $this->isLoyal;
    }

    /**
     * Set isMilitary
     *
     * @param bool $isMilitary
     *
     * @return Card
     */
    public function setIsMilitary($isMilitary)
    {
        $this->isMilitary = $isMilitary;

        return $this;
    }

    /**
     * Get isMilitary
     *
     * @return bool
     */
    public function getIsMilitary()
    {
        return $this->isMilitary;
    }

    /**
     * Set isIntrigue
     *
     * @param bool $isIntrigue
     *
     * @return Card
     */
    public function setIsIntrigue($isIntrigue)
    {
        $this->isIntrigue = $isIntrigue;

        return $this;
    }

    /**
     * Get isIntrigue
     *
     * @return bool
     */
    public function getIsIntrigue()
    {
        return $this->isIntrigue;
    }

    /**
     * Set isPower
     *
     * @param bool $isPower
     *
     * @return Card
     */
    public function setIsPower($isPower)
    {
        $this->isPower = $isPower;

        return $this;
    }

    /**
     * Get isPower
     *
     * @return bool
     */
    public function getIsPower()
    {
        return $this->isPower;
    }

    /**
     * Set octgnId
     *
     * @param bool $octgnId
     *
     * @return Card
     */
    public function setOctgnId($octgnId)
    {
        $this->octgnId = $octgnId;

        return $this;
    }

    /**
     * Get octgnId
     *
     * @return bool
     */
    public function getOctgnId()
    {
        return $this->octgnId;
    }

    /**
     * Add review
     *
     * @param Review $review
     *
     * @return Card
     */
    public function addReview(Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set pack
     *
     * @param Pack $pack
     *
     * @return Card
     */
    public function setPack(Pack $pack = null)
    {
        $this->pack = $pack;

        return $this;
    }

    /**
     * Get pack
     *
     * @return Pack
     */
    public function getPack()
    {
        return $this->pack;
    }

    /**
     * Set type
     *
     * @param Type $type
     *
     * @return Card
     */
    public function setType(Type $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set faction
     *
     * @param Faction $faction
     *
     * @return Card
     */
    public function setFaction(Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Get faction
     *
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
     * Set designer
     *
     * @param string $designer
     *
     * @return Card
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;

        return $this;
    }

    /**
     * Get designer
     *
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
     *
     * @return self
     */
    public function setIsMultiple(bool $isMultiple): self
    {
        $this->isMultiple = $isMultiple;

        return $this;
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
     *
     * @return self
     */
    public function setImageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
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
     * @return array
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
     * @param string $serialized
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

    protected function snakeToCamel($snake)
    {
        $parts = explode('_', $snake);

        return implode('', array_map('ucfirst', $parts));
    }
}
