<?php

namespace App\Entity;

/**
 * @package App\Entity
 */
interface DeckslotInterface extends SlotInterface
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
     * @param DeckInterface $deck
     */
    public function setDeck(DeckInterface $deck = null);

    /**
     * @return DeckInterface
     */
    public function getDeck();
}
