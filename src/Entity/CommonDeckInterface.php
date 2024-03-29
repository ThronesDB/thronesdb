<?php

namespace App\Entity;

use App\Classes\SlotCollectionInterface;
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
     * @return FactionInterface
     */
    public function getFaction();

    /**
     * @param FactionInterface|null $faction
     */
    public function setFaction(FactionInterface $faction = null);

    /**
     * @return SlotCollectionInterface
     */
    public function getSlots();

    /**
     * @return UserInterface|null
     */
    public function getUser();

    /**
     * @param UserInterface|null $user
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return array
     */
    public function getTextExport();

    /**
     * @return array
     */
    public function getCycleOrderExport();
}
