<?php

namespace App\Entity;

use App\Model\SlotCollectionDecorator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="deck")
 * @ORM\Entity
 */
class Deck extends CommonDeck implements DeckInterface
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
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="decks")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var FactionInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Faction")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="faction_id", referencedColumnName="id")
     * })
     */
    protected $faction;

    /**
     * @var PackInterface
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
     * @inheritdoc
     */
    public function setProblem($problem)
    {
        $this->problem = $problem;
    }

    /**
     * @inheritdoc
     */
    public function getProblem()
    {
        return $this->problem;
    }

    /**
     * @inheritdoc
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function addSlot(DeckslotInterface $slot)
    {
        $this->slots->add($slot);
    }

    /**
     * @inheritdoc
     */
    public function removeSlot(DeckslotInterface $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * @inheritdoc
     */
    public function addChild(Decklist $child)
    {
        $this->children->add($child);
    }

    /**
     * @inheritdoc
     */
    public function removeChild(Decklist $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     */
    public function addChange(DeckchangeInterface $change)
    {
        $this->changes->add($change);
    }

    /**
     * @inheritdoc
     */
    public function removeChange(DeckchangeInterface $change)
    {
        $this->changes->removeElement($change);
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setUser(UserInterface $user = null)
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
     * @inheritdoc
     */
    public function setFaction(FactionInterface $faction = null)
    {
        $this->faction = $faction;
    }

    /**
     * @inheritdoc
     */
    public function setLastPack(PackInterface $lastPack = null)
    {
        $this->lastPack = $lastPack;
    }

    /**
     * @inheritdoc
     */
    public function getLastPack()
    {
        return $this->lastPack;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Decklist $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setMajorVersion($majorVersion)
    {
        $this->majorVersion = $majorVersion;
    }

    /**
     * @inheritdoc
     */
    public function getMajorVersion()
    {
        return $this->majorVersion;
    }

    /**
     * @inheritdoc
     */
    public function setMinorVersion($minorVersion)
    {
        $this->minorVersion = $minorVersion;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
     */
    public function getHistory()
    {
        $slots = $this->getSlots();
        $cards = $slots->getContent();

        $snapshots = [];

        /*
         * All changes, with the newest at position 0
         */
        $changes = $this->getChanges();

        /*
         * Saved changes, with the newest at position 0
         * @var DeckchangeInterface[] $savedChanges
         */
        $savedChanges = [];

        /*
         * Unsaved changes, with the oldest at position 0
         * @var DeckchangeInterface[] $unsavedChanges
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
     * @inheritdoc
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
     * @inheritdoc
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @inheritdoc
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
