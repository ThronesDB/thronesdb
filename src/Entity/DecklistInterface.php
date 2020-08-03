<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use JsonSerializable;

/**
 * @package App\Entity
 */
interface DecklistInterface extends CommonDeckInterface, JsonSerializable
{
    /**
     * @param string $nameCanonical
     */
    public function setNameCanonical($nameCanonical);

    /**
     * @return string
     */
    public function getNameCanonical();

    /**
     * @param string $descriptionHtml
     */
    public function setDescriptionHtml($descriptionHtml);

    /**
     * @return string
     */
    public function getDescriptionHtml();

    /**
     * @param string $signature
     */
    public function setSignature($signature);

    /**
     * @return string
     */
    public function getSignature();

    /**
     * @param int $nbVotes
     */
    public function setNbVotes($nbVotes);

    /**
     * @return int
     */
    public function getnbVotes();

    /**
     * @param int $nbFavorites
     */
    public function setNbFavorites($nbFavorites);

    /**
     * @return int
     */
    public function getNbFavorites();

    /**
     * @param int $nbComments
     */
    public function setNbComments($nbComments);

    /**
     * @return int
     */
    public function getNbComments();

    /**
     * @param DecklistslotInterface $slot
     */
    public function addSlot(DecklistslotInterface $slot);

    /**
     * @param DecklistslotInterface $slot
     */
    public function removeSlot(DecklistslotInterface $slot);

    /**
     * @param CommentInterface $comment
     */
    public function addComment(CommentInterface $comment);

    /**
     * @param CommentInterface $comment
     */
    public function removeComment(CommentInterface $comment);

    /**
     * @return Collection
     */
    public function getComments();

    /**
     * @param DecklistInterface $successor
     */
    public function addSuccessor(DecklistInterface $successor);

    /**
     * @param DecklistInterface $successor
     */
    public function removeSuccessor(DecklistInterface $successor);

    /**
     * @return Collection
     */
    public function getSuccessors();

    /**
     * @param DeckInterface $child
     */
    public function addChild(DeckInterface $child);

    /**
     * @param DeckInterface $child
     */
    public function removeChild(DeckInterface $child);

    /**
     * @return Collection
     */
    public function getChildren();

    /**
     * @param PackInterface|null $lastPack
     */
    public function setLastPack(PackInterface $lastPack = null);

    /**
     * @return PackInterface
     */
    public function getLastPack();

    /**
     * @param DeckInterface|null $parent
     */
    public function setParent(DeckInterface $parent = null);

    /**
     * @return DeckInterface
     */
    public function getParent();

    /**
     * @param DecklistInterface|null $precedent
     */
    public function setPrecedent(DecklistInterface $precedent = null);

    /**
     * @return Decklist
     */
    public function getPrecedent();

    /**
     * @param TournamentInterface|null $tournament
     */
    public function setTournament(TournamentInterface $tournament = null);

    /**
     * @return TournamentInterface
     */
    public function getTournament();

    /**
     * @param UserInterface $favorite
     */
    public function addFavorite(UserInterface $favorite);

    /**
     * @param UserInterface $favorite
     */
    public function removeFavorite(UserInterface $favorite);

    /**
     * @return Collection
     */
    public function getFavorites();

    /**
     * @param UserInterface $vote
     */
    public function addVote(UserInterface $vote);

    /**
     * @param UserInterface $vote
     */
    public function removeVote(UserInterface $vote);

    /**
     * @return Collection
     */
    public function getVotes();

    /**
     * @param string $version
     */
    public function setVersion($version);
}
