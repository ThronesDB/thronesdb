<?php

namespace App\Entity;

use App\Model\SlotCollectionInterface;
use DateTime;

/**
 * A common interface for both deck and decklist.
 * @package App\Entity
 */
interface CommonDeckInterface
{
    /**
     * @return int
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param DateTime $dateCreation
     * @return mixed
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateUpdate();

    /**
     * @param DateTime $dateUpdate
     * @return mixed
     */
    public function setDateUpdate($dateUpdate);

    /**
     * @return string
     */
    public function getDescriptionMd();

    /**
     * @param $descriptionMd
     * @return mixed
     */
    public function setDescriptionMd($descriptionMd);

    /**
     * @return mixed
     */
    public function getVersion();

    /**
     * @return Faction
     */
    public function getFaction();

    /**
     * @param Faction|null $faction
     * @return mixed
     */
    public function setFaction(Faction $faction = null);

    /**
     * @return SlotCollectionInterface
     */
    public function getSlots();

    /**
     * @return User|null
     */
    public function getUser();

    /**
     * @param User|null $user
     * @return mixed
     */
    public function setUser(User $user = null);
}
