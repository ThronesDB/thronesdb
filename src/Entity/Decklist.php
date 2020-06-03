<?php

namespace App\Entity;

use App\Model\SlotCollectionDecorator;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;

/**
 * Decklist
 *
 * @ORM\Table(name="decklist")
 * @ORM\Entity(repositoryClass="App\Repository\DecklistRepository")
 */
class Decklist extends BaseDeck implements JsonSerializable
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="decklists")
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
     * @var Deck
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="children")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="parent_deck_id", referencedColumnName="id")
     * })
     */
    protected $parent;

    /**
     * @var Decklist
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Decklist", inversedBy="successors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="precedent_decklist_id", referencedColumnName="id")
     * })
     */
    protected $precedent;

    /**
     * @var Tournament
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
     * Set name
     *
     * @param string $name
     *
     * @return Decklist
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set nameCanonical
     *
     * @param string $nameCanonical
     *
     * @return Decklist
     */
    public function setNameCanonical($nameCanonical)
    {
        $this->nameCanonical = $nameCanonical;

        return $this;
    }

    /**
     * Get nameCanonical
     *
     * @return string
     */
    public function getNameCanonical()
    {
        return $this->nameCanonical;
    }

    /**
     * Set dateCreation
     *
     * @param DateTime $dateCreation
     *
     * @return Decklist
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Set dateUpdate
     *
     * @param DateTime $dateUpdate
     *
     * @return Decklist
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Set descriptionMd
     *
     * @param string $descriptionMd
     *
     * @return Decklist
     */
    public function setDescriptionMd($descriptionMd)
    {
        $this->descriptionMd = $descriptionMd;

        return $this;
    }

    /**
     * Set descriptionHtml
     *
     * @param string $descriptionHtml
     *
     * @return Decklist
     */
    public function setDescriptionHtml($descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;

        return $this;
    }

    /**
     * Get descriptionHtml
     *
     * @return string
     */
    public function getDescriptionHtml()
    {
        return $this->descriptionHtml;
    }

    /**
     * Set signature
     *
     * @param string $signature
     *
     * @return Decklist
     */
    public function setSignature($signature)
    {
        $this->signature = $signature;

        return $this;
    }

    /**
     * Get signature
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Set nbVotes
     *
     * @param int $nbVotes
     *
     * @return Decklist
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;

        return $this;
    }

    /**
     * Get nbVotes
     *
     * @return int
     */
    public function getnbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * Set nbFavorites
     *
     * @param int $nbFavorites
     *
     * @return Decklist
     */
    public function setNbFavorites($nbFavorites)
    {
        $this->nbFavorites = $nbFavorites;

        return $this;
    }

    /**
     * Get nbFavorites
     *
     * @return int
     */
    public function getNbFavorites()
    {
        return $this->nbFavorites;
    }

    /**
     * Set nbComments
     *
     * @param int $nbComments
     *
     * @return Decklist
     */
    public function setNbComments($nbComments)
    {
        $this->nbComments = $nbComments;

        return $this;
    }

    /**
     * Get nbComments
     *
     * @return int
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
     * Add slot
     *
     * @param Decklistslot $slot
     *
     * @return Decklist
     */
    public function addSlot(Decklistslot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot
     *
     * @param Decklistslot $slot
     */
    public function removeSlot(Decklistslot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Add comment
     *
     * @param Comment $comment
     *
     * @return Decklist
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add successor
     *
     * @param Decklist $successor
     *
     * @return Decklist
     */
    public function addSuccessor(Decklist $successor)
    {
        $this->successors[] = $successor;

        return $this;
    }

    /**
     * Remove successor
     *
     * @param Decklist $successor
     */
    public function removeSuccessor(Decklist $successor)
    {
        $this->successors->removeElement($successor);
    }

    /**
     * Get successors
     *
     * @return Collection
     */
    public function getSuccessors()
    {
        return $this->successors;
    }

    /**
     * Add child
     *
     * @param Deck $child
     *
     * @return Decklist
     */
    public function addChild(Deck $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param Deck $child
     */
    public function removeChild(Deck $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return Collection
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
     * Set user
     *
     * @param User $user
     *
     * @return Decklist
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Faction
     */
    public function getFaction()
    {
        return $this->faction;
    }

    /**
     * Set faction
     *
     * @param Faction $faction
     *
     * @return Decklist
     */
    public function setFaction(Faction $faction = null)
    {
        $this->faction = $faction;

        return $this;
    }

    /**
     * Set lastPack
     *
     * @param Pack $lastPack
     *
     * @return Decklist
     */
    public function setLastPack(Pack $lastPack = null)
    {
        $this->lastPack = $lastPack;

        return $this;
    }

    /**
     * Get lastPack
     *
     * @return Pack
     */
    public function getLastPack()
    {
        return $this->lastPack;
    }

    /**
     * Set parent
     *
     * @param Deck $parent
     *
     * @return Decklist
     */
    public function setParent(Deck $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return Deck
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set precedent
     *
     * @param Decklist $precedent
     *
     * @return Decklist
     */
    public function setPrecedent(Decklist $precedent = null)
    {
        $this->precedent = $precedent;

        return $this;
    }

    /**
     * Get precedent
     *
     * @return Decklist
     */
    public function getPrecedent()
    {
        return $this->precedent;
    }

    /**
     * Set tournament
     *
     * @param Tournament $tournament
     *
     * @return Decklist
     */
    public function setTournament(Tournament $tournament = null)
    {
        $this->tournament = $tournament;

        return $this;
    }

    /**
     * Get tournament
     *
     * @return Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * Add favorite
     *
     * @param User $favorite
     *
     * @return Decklist
     */
    public function addFavorite(User $favorite)
    {
        $this->favorites[] = $favorite;

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param User $favorite
     */
    public function removeFavorite(User $favorite)
    {
        $this->favorites->removeElement($favorite);
    }

    /**
     * Get favorites
     *
     * @return Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * Add vote
     *
     * @param User $vote
     *
     * @return Decklist
     */
    public function addVote(User $vote)
    {
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param User $vote
     */
    public function removeVote(User $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return Decklist
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
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
