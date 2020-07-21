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
     * @param int $id
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateUpdate();

    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate);

    /**
     * @return string
     */
    public function getDescriptionMd();

    /**
     * @param $descriptionMd
     */
    public function setDescriptionMd($descriptionMd);

    /**
     * @return string
     */
    public function getVersion();

    /**
     * @return Faction
     */
    public function getFaction();

    /**
     * @param Faction|null $faction
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
     */
    public function setUser(User $user = null);

    /**
     * Transforms the given object into an associative array.
     * @return array
     */
    public function getArrayExport();

    /**
     * @return array
     */
    public function getTextExport();

    /**
     * @return array
     */
    public function getCycleOrderExport();

    /**
     * @return bool
     * @see SlotCollectionInterface::isLegalForMelee()
     */
    public function isLegalForMelee();

    /**
     * @return bool
     * @see SlotCollectionInterface::isLegalForJoust()
     */
    public function isLegalForJoust();
}
