<?php

namespace App\Entity;

use App\Classes\SlotCollectionDecorator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="decklist")
 * @ORM\Entity(repositoryClass="App\Repository\DecklistRepository")
 */
class Decklist extends CommonDeck implements JsonSerializable, DecklistInterface
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
     * @var string
     *
     * @ORM\Column(name="name_canonical", type="string", length=255)
     */
    protected $nameCanonical;

    /**
     * @var string|null
     *
     * @ORM\Column(name="description_html", type="text", nullable=true)
     */
    protected $descriptionHtml;

    /**
     * @var string
     *
     * @ORM\Column(name="signature", type="string", length=32)
     */
    protected $signature;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_votes", type="integer")
     */
    protected $nbVotes;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_favorites", type="integer")
     */
    protected $nbFavorites;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_comments", type="integer")
     */
    protected $nbComments;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", length=8)
     */
    protected $version;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Decklistslot", mappedBy="decklist", cascade={"persist","remove"})
     */
    protected $slots;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="decklist", cascade={"persist","remove"})
     * @ORM\OrderBy({
     *     "dateCreation"="ASC"
     * })
     */
    protected $comments;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Decklist", mappedBy="precedent")
     * @ORM\OrderBy({
     *     "dateCreation"="ASC"
     * })
     */
    protected $successors;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Deck", mappedBy="parent")
     */
    protected $children;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="decklists")
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
     * @var DeckInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_deck_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var DecklistInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Decklist", inversedBy="successors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="precedent_decklist_id", referencedColumnName="id")
     * })
     */
    protected $precedent;

    /**
     * @var TournamentInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Tournament", inversedBy="decklists")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tournament_id", referencedColumnName="id")
     * })
     */
    protected $tournament;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="favorites")
     * @ORM\JoinTable(name="favorite",
     *   joinColumns={
     *     @ORM\JoinColumn(name="decklist_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $favorites;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="votes")
     * @ORM\JoinTable(name="vote",
     *   joinColumns={
     *     @ORM\JoinColumn(name="decklist_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $votes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->successors = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->votes = new ArrayCollection();
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
    public function setNameCanonical($nameCanonical)
    {
        $this->nameCanonical = $nameCanonical;
    }

    /**
     * @inheritdoc
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
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
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;
    }

    /**
     * @inheritdoc
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }

    /**
     * @inheritdoc
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;
    }

    /**
     * @inheritdoc
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @inheritdoc
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;
    }

    /**
     * @inheritdoc
     */
    public function getnbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * @inheritdoc
     */
    public function setNbFavorites($nbFavorites)
    {
        $this->nbFavorites = $nbFavorites;
    }

    /**
     * @inheritdoc
     */
    public function getNbFavorites()
    {
        return $this->nbFavorites;
    }

    /**
     * @inheritdoc
     */
    public function setNbComments($nbComments)
    {
        $this->nbComments = $nbComments;
    }

    /**
     * @inheritdoc
     */
    public function getNbComments()
    {
        return $this->nbComments;
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
    public function addSlot(DecklistslotInterface $slot)
    {
        $this->slots->add($slot);
    }

    /**
     * @inheritdoc
     */
    public function removeSlot(DecklistslotInterface $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * @inheritdoc
     */
    public function addComment(CommentInterface $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @inheritdoc
     */
    public function removeComment(CommentInterface $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * @inheritdoc
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @inheritdoc
     */
    public function addSuccessor(DecklistInterface $successor)
    {
        $this->successors->add($successor);
    }

    /**
     * @inheritdoc
     */
    public function removeSuccessor(DecklistInterface $successor)
    {
        $this->successors->removeElement($successor);
    }

    /**
     * @inheritdoc
     */
    public function getSuccessors()
    {
        return $this->successors;
    }

    /**
     * @inheritdoc
     */
    public function addChild(DeckInterface $child)
    {
        $this->children->add($child);
    }

    /**
     * @inheritdoc
     */
    public function removeChild(DeckInterface $child)
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
    public function setParent(DeckInterface $parent = null)
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
    public function setPrecedent(DecklistInterface $precedent = null)
    {
        $this->precedent = $precedent;
    }

    /**
     * @inheritdoc
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }

    /**
     * @inheritdoc
     */
    public function setTournament(TournamentInterface $tournament = null)
    {
        $this->tournament = $tournament;
    }

    /**
     * @inheritdoc
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * @inheritdoc
     */
    public function addFavorite(UserInterface $favorite)
    {
        $this->favorites->add($favorite);
    }

    /**
     * @inheritdoc
     */
    public function removeFavorite(UserInterface $favorite)
    {
        $this->favorites->removeElement($favorite);
    }

    /**
     * @inheritdoc
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * @inheritdoc
     */
    public function addVote(UserInterface $vote)
    {
        $this->votes->add($vote);
    }

    /**
     * @inheritdoc
     */
    public function removeVote(UserInterface $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * @inheritdoc
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @inheritdoc
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return parent::getArrayExport();
    }
}
