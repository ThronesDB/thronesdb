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
     * @param Decklist $decklist
     */
    public function addDecklist(Decklist $decklist);

    /**
     * @param Decklist $decklist
     */
    public function removeDecklist(Decklist $decklist);

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
     * @param Review $review
     */
    public function addReview(Review $review);

    /**
     * @param Review $review
     */
    public function removeReview(Review $review);

    /**
     * @return Collection
     */
    public function getReviews();

    /**
     * @param Decklist $favorite
     */
    public function addFavorite(Decklist $favorite);

    /**
     * @param Decklist $favorite
     */
    public function removeFavorite(Decklist $favorite);

    /**
     * @return Collection
     */
    public function getFavorites();

    /**
     * @param Decklist $vote
     */
    public function addVote(Decklist $vote);

    /**
     * @param Decklist $vote
     */
    public function removeVote(Decklist $vote);

    /**
     * @return Collection
     */
    public function getVotes();

    /**
     * @param Review $reviewvote
     */
    public function addReviewvote(Review $reviewvote);

    /**
     * @param Review $reviewvote
     */
    public function removeReviewvote(Review $reviewvote);

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
