<?php

namespace AppBundle\Entity;

use AppBundle\Model\ExportableDeck;

/**
 * Class Decklist
 * @package AppBundle\Entity
 */
class Decklist extends ExportableDeck implements \JsonSerializable
{
    /**
     * @var string
     */
    private $nameCanonical;

    /**
     * @var string
     */
    private $descriptionHtml;

    /**
     * @var string
     */
    private $signature;

    /**
     * @var integer
     */
    private $nbVotes;

    /**
     * @var integer
     */
    private $nbFavorites;

    /**
     * @var integer
     */
    private $nbComments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $comments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $successors;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $children;

    /**
     * @var Pack
     */
    private $lastPack;

    /**
     * @var Deck
     */
    private $parent;

    /**
     * @var Decklist
     */
    private $precedent;

    /**
     * @var Tournament
     */
    private $tournament;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $favorites;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $votes;

    /**
     * @var integer
     */
    private $version;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->successors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favorites = new \Doctrine\Common\Collections\ArrayCollection();
        $this->votes = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \DateTime $dateCreation
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
     * @param \DateTime $dateUpdate
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
     * @param integer $nbVotes
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
     * @return integer
     */
    public function getnbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * Set nbFavorites
     *
     * @param integer $nbFavorites
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
     * @return integer
     */
    public function getNbFavorites()
    {
        return $this->nbFavorites;
    }

    /**
     * Set nbComments
     *
     * @param integer $nbComments
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
     * @return integer
     */
    public function getNbComments()
    {
        return $this->nbComments;
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
     * @return \Doctrine\Common\Collections\Collection
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
     * @return \Doctrine\Common\Collections\Collection
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
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
     * @return \AppBundle\Entity\Decklist
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
     * @return \Doctrine\Common\Collections\Collection
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
     * @return \Doctrine\Common\Collections\Collection
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
