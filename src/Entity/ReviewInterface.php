<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;

/**
 * Interface ReviewInterface
 * @package App\Entity
 */
interface ReviewInterface
{
    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

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
     * @param string $textMd
     */
    public function setTextMd($textMd);

    /**
     * @return string
     */
    public function getTextMd();

    /**
     * @param string $textHtml
     */
    public function setTextHtml($textHtml);

    /**
     * @return string
     */
    public function getTextHtml();

    /**
     * @param int $nbVotes
     */
    public function setNbVotes($nbVotes);

    /**
     * @return int
     */
    public function getNbVotes();

    /**
     * @param ReviewcommentInterface $comment
     */
    public function addComment(ReviewcommentInterface $comment);

    /**
     * @param ReviewcommentInterface $comment
     */
    public function removeComment(ReviewcommentInterface $comment);

    /**
     * @return Collection
     */
    public function getComments();

    /**
     * @param CardInterface $card
     */
    public function setCard(CardInterface $card = null);

    /**
     * @return CardInterface
     */
    public function getCard();

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return UserInterface
     */
    public function getUser();

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
}
