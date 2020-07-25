<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * Interface UserInterface
 * @package App\Entity
 */
interface UserInterface extends SymfonyUserInterface
{
    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate);

    /**
     * @return DateTime
     */
    public function getDateUpdate();

    /**
     * @param int $reputation
     */
    public function setReputation($reputation);

    /**
     * @return int
     */
    public function getReputation();

    /**
     * @param string $resume
     */
    public function setResume($resume);

    /**
     * @return string
     */
    public function getResume();

    /**
     * @param string $color
     */
    public function setColor($color);

    /**
     * @return string
     */
    public function getColor();

    /**
     * @param int $donation
     */
    public function setDonation($donation);

    /**
     * @return int
     */
    public function getDonation();

    /**
     * @param bool $isNotifAuthor
     */
    public function setIsNotifAuthor($isNotifAuthor);

    /**
     * @return bool
     */
    public function getIsNotifAuthor();

    /**
     * @param bool $isNotifCommenter
     */
    public function setIsNotifCommenter($isNotifCommenter);

    /**
     * @return bool
     */
    public function getIsNotifCommenter();

    /**
     * @param bool $isNotifMention
     */
    public function setIsNotifMention($isNotifMention);

    /**
     * @return bool
     */
    public function getIsNotifMention();

    /**
     * @param bool $isNotifFollow
     */
    public function setIsNotifFollow($isNotifFollow);

    /**
     * @return bool
     */
    public function getIsNotifFollow();

    /**
     * @param bool $isNotifSuccessor
     */
    public function setIsNotifSuccessor($isNotifSuccessor);

    /**
     * @return bool
     */
    public function getIsNotifSuccessor();

    /**
     * @param bool $isShareDecks
     */
    public function setIsShareDecks($isShareDecks);

    /**
     * @return bool
     */
    public function getIsShareDecks();

    /**
     * @param DeckInterface $deck
     */
    public function addDeck(DeckInterface $deck);

    /**
     * @param DeckInterface $deck
     */
    public function removeDeck(DeckInterface $deck);

    /**
     * @return Collection
     */
    public function getDecks();

    /**
     * @param DecklistInterface $decklist
     */
    public function addDecklist(DecklistInterface $decklist);

    /**
     * @param DecklistInterface $decklist
     */
    public function removeDecklist(DecklistInterface $decklist);

    /**
     * @return Collection
     */
    public function getDecklists();

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
     * @param ReviewInterface $review
     */
    public function addReview(ReviewInterface $review);

    /**
     * @param ReviewInterface $review
     */
    public function removeReview(ReviewInterface $review);

    /**
     * @return Collection
     */
    public function getReviews();

    /**
     * @param DecklistInterface $favorite
     */
    public function addFavorite(DecklistInterface $favorite);

    /**
     * @param DecklistInterface $favorite
     */
    public function removeFavorite(DecklistInterface $favorite);

    /**
     * @return Collection
     */
    public function getFavorites();

    /**
     * @param DecklistInterface $vote
     */
    public function addVote(DecklistInterface $vote);

    /**
     * @param DecklistInterface $vote
     */
    public function removeVote(DecklistInterface $vote);

    /**
     * @return Collection
     */
    public function getVotes();

    /**
     * @param ReviewInterface $reviewvote
     */
    public function addReviewvote(ReviewInterface $reviewvote);

    /**
     * @param ReviewInterface $reviewvote
     */
    public function removeReviewvote(ReviewInterface $reviewvote);

    /**
     * @return Collection
     */
    public function getReviewvotes();

    /**
     * @param UserInterface $following
     */
    public function addFollowing(UserInterface $following);

    /**
     * @param UserInterface $following
     */
    public function removeFollowing(UserInterface $following);

    /**
     * @return Collection
     */
    public function getFollowing();

    /**
     * @param UserInterface $follower
     */
    public function addFollower(UserInterface $follower);

    /**
     * @param UserInterface $follower
     */
    public function removeFollower(UserInterface $follower);

    /**
     * @return Collection
     */
    public function getFollowers();

    /**
     * @return float|int
     */
    public function getMaxNbDecks();
}
