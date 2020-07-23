<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Serializable;

/**
 * @package App\Entity
 */
interface PackInterface extends Serializable
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
     * @param string $code
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param int $size
     */
    public function setSize($size);

    /**
     * @return int
     */
    public function getSize();

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
     * @param DateTime $dateRelease
     */
    public function setDateRelease($dateRelease);

    /**
     * @return DateTime
     */
    public function getDateRelease();

    /**
     * @param int $cgdbId
     */
    public function setCgdbId($cgdbId);

    /**
     * @return int
     */
    public function getCgdbId();

    /**
     * @param CardInterface $card
     */
    public function addCard(CardInterface $card);

    /**
     * @param CardInterface $card
     */
    public function removeCard(CardInterface $card);

    /**
     * @return Collection
     */
    public function getCards();

    /**
     * @param CycleInterface $cycle
     */
    public function setCycle(CycleInterface $cycle = null);

    /**
     * @return CycleInterface
     */
    public function getCycle();
}
