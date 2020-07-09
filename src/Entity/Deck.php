<?php

namespace App\Entity;

use App\Model\SlotCollectionDecorator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;

/**
 * Deck
 *
 * @ORM\Table(name="deck")
 * @ORM\Entity
 */
class Deck extends BaseDeck implements JsonSerializable
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

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
     * @var string|null
     *
     * @ORM\Column(name="description_md", type="text", nullable=true)
     */
    protected $descriptionMd;

    /**
     * @var string|null
     *
     * @ORM\Column(name="problem", type="string", length=255, nullable=true)
     */
    protected $problem;

    /**
     * @var string|null
     *
     * @ORM\Column(name="tags", type="string", length=4000, nullable=true)
     */
    protected $tags;

    /**
     * @var int
     *
     * @ORM\Column(name="major_version", type="integer", nullable=false)
     */
    protected $majorVersion;

    /**
     * @var int
     *
     * @ORM\Column(name="minor_version", type="integer", nullable=false)
     */
    protected $minorVersion;

    /**
     * @var UuidInterface|null
     *
     * @ORM\Column(name="uuid", type="uuid", nullable=true, unique=true)
     */
    protected $uuid;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Deckslot", mappedBy="deck", cascade={"persist","remove"})
     */
    protected $slots;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Decklist", mappedBy="parent")
     * @ORM\OrderBy({
     *     "dateCreation"="DESC"
     * })
     */
    protected $children;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Deckchange", mappedBy="deck", cascade={"persist","remove"})
     * @ORM\OrderBy({
     *     "dateCreation"="DESC",
     *     "isSaved"="DESC"
     * })
     */
    protected $changes;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="decks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var Faction
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Faction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="faction_id", referencedColumnName="id")
     * })
     */
    protected $faction;

    /**
     * @var Pack
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Pack")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="last_pack_id", referencedColumnName="id")
     * })
     */
    protected $lastPack;

    /**
     * @var Decklist
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Decklist", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_decklist_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->changes = new ArrayCollection();
        $this->minorVersion = 0;
        $this->majorVersion = 0;
    }

    /**
     * @param $id
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
     * @param string $name
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
     * @param DateTime $dateCreation
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
     * @param DateTime $dateUpdate
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
     * @param string $descriptionMd
     */
    public function setDescriptionMd($descriptionMd)
    {
        $this->descriptionMd = $descriptionMd;
    }

    /**
     * @inheritdoc
     */
    public function getDescriptionMd()
    {
        return $this->descriptionMd;
    }

    /**
     * @param string $problem
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;
    }

    /**
     * @return string
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * @param string $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @inheritdoc
     */
    public function getSlots()
    {
        return new SlotCollectionDecorator($this->slots);
    }

    /**
     * @param Deckslot $slot
     */
    public function addSlot(Deckslot $slot)
    {
        $this->slots->add($slot);
    }

    /**
     * @param Deckslot $slot
     */
    public function removeSlot(Deckslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * @param Decklist $child
     */
    public function addChild(Decklist $child)
    {
        $this->children->add($child);
    }

    /**
     * @param Decklist $child
     */
    public function removeChild(Decklist $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @return Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Deckchange $change
     */
    public function addChange(Deckchange $change)
    {
        $this->changes->add($change);
    }

    /**
     * @param Deckchange $change
     */
    public function removeChange(Deckchange $change)
    {
        $this->changes->removeElement($change);
    }

    /**
     * @return Collection
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * @param Faction $faction
     */
    public function setFaction(Faction $faction = null)
    {
        $this->faction = $faction;
    }

    /**
     * @param Pack $lastPack
     */
    public function setLastPack(Pack $lastPack = null)
    {
        $this->lastPack = $lastPack;
    }

    /**
     * @return Pack
     */
    public function getLastPack()
    {
        return $this->lastPack;
    }

    /**
     * @param Decklist $parent
     */
    public function setParent(Decklist $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return Decklist
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $majorVersion
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;
    }

    /**
     * @return int
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }

    /**
     * @param int $minorVersion
     */
    public function setMinorVersion($minorVersion)
    {
        $this->minorVersion = $minorVersion;
    }

    /**
     * @return int
     */
    public function getMinorVersion()
    {
        return $this->minorVersion;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->majorVersion . "." . $this->minorVersion;
    }

    /**
     * @return array
     */
    public function getHistory()
    {
        $slots = $this->getSlots();
        $cards = $slots->getContent();

        $snapshots = [];

        /**
         * All changes, with the newest at position 0
         */
        $changes = $this->getChanges();

        /**
         * Saved changes, with the newest at position 0
         * @var $savedChanges Deckchange[]
         */
        $savedChanges = [];

        /**
         * Unsaved changes, with the oldest at position 0
         * @var $unsavedChanges Deckchange[]
         */
        $unsavedChanges = [];

        foreach ($changes as $change) {
            if ($change->getIsSaved()) {
                array_push($savedChanges, $change);
            } else {
                array_unshift($unsavedChanges, $change);
            }
        }

        // recreating the versions with the variation info, starting from $preversion
        $preversion = $cards;

        foreach ($savedChanges as $change) {
            $variation = json_decode($change->getVariation(), true);

            $row = [
                'variation' => $variation,
                'is_saved' => $change->getIsSaved(),
                'version' => $change->getVersion(),
                'content' => $preversion,
                'date_creation' => $change->getDateCreation()->format('c'),
            ];
            array_unshift($snapshots, $row);

            // applying variation to create 'next' (older) preversion
            foreach ($variation[0] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    continue;
                }
                $preversion[$code] = $preversion[$code] - $qty;
                if ($preversion[$code] == 0) {
                    unset($preversion[$code]);
                }
            }
            foreach ($variation[1] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    $preversion[$code] = 0;
                }
                $preversion[$code] = $preversion[$code] + $qty;
            }
            ksort($preversion);
        }

        // add last know version with empty diff
        $row = [
            'variation' => null,
            'is_saved' => true,
            'version' => "0.0",
            'content' => $preversion,
            'date_creation' => $this->getDateCreation()->format('c')
        ];
        array_unshift($snapshots, $row);

        // recreating the snapshots with the variation info, starting from $postversion
        $postversion = $cards;
        foreach ($unsavedChanges as $change) {
            $variation = json_decode($change->getVariation(), true);
            $row = [
                'variation' => $variation,
                'is_saved' => $change->getIsSaved(),
                'version' => $change->getVersion(),
                'date_creation' => $change->getDateCreation()->format('c'),
            ];

            // applying variation to postversion
            foreach ($variation[0] as $code => $qty) {
                if (!isset($postversion[$code])) {
                    $postversion[$code] = 0;
                }
                $postversion[$code] = $postversion[$code] + $qty;
            }
            foreach ($variation[1] as $code => $qty) {
                if (!isset($preversion[$code])) {
                    continue;
                }
                $postversion[$code] = $postversion[$code] - $qty;
                if ($postversion[$code] == 0) {
                    unset($postversion[$code]);
                }
            }
            ksort($postversion);

            // add postversion with variation that lead to it
            $row['content'] = $postversion;
            array_push($snapshots, $row);
        }

        return $snapshots;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $array = parent::getArrayExport();
        $array['problem'] = $this->getProblem();
        $array['tags'] = $this->getTags();
        $array['uuid'] = $this->getUuid();
        return $array;
    }

    /**
     * @return bool
     */
    public function getIsUnsaved()
    {
        $changes = $this->getChanges();

        foreach ($changes as $change) {
            if (!$change->getIsSaved()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param UuidInterface $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UuidInterface|null
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
