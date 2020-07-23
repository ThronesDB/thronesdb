<?php

namespace App\Entity;

use DateTime;

/**
 * @package App\Entity
 */
interface ReviewcommentInterface
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
     * @param string $text
     */
    public function setText($text);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param Review $review
     */
    public function setReview(Review $review = null);

    /**
     * @return Review
     */
    public function getReview();
}
