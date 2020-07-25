<?php

namespace App\Entity;

use DateTime;

/**
 * Interface CommentInterface
 * @package App\Entity
 */
interface CommentInterface
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
     * @param string $text
     */
    public function setText($text);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param bool $isHidden
     */
    public function setIsHidden($isHidden);

    /**
     * @return bool
     */
    public function getIsHidden();

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param DecklistInterface $decklist
     */
    public function setDecklist(DecklistInterface $decklist = null);

    /**
     * @return DecklistInterface
     */
    public function getDecklist();
}
