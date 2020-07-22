<?php

namespace App\Entity;

use DateTime;

/**
 * @package App\Entity
 */
interface DeckchangeInterface
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
     * @param string $variation
     */
    public function setVariation($variation);

    /**
     * @return string
     */
    public function getVariation();

    /**
     * @param bool $isSaved
     */
    public function setIsSaved($isSaved);

    /**
     * @return bool
     */
    public function getIsSaved();

    /**
     * @param DeckInterface $deck
     */
    public function setDeck(DeckInterface $deck = null);

    /**
     * @return DeckInterface
     */
    public function getDeck();

    /**
     * @param string $version
     */
    public function setVersion($version);

    /**
     * @return string
     */
    public function getVersion();
}
