<?php

namespace App\Entity;

/**
 * @package App\Entity
 */
interface DecklistslotInterface extends SlotInterface
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
     * @param Decklist $decklist
     */
    public function setDecklist(Decklist $decklist = null);

    /**
     * @return Decklist
     */
    public function getDecklist();
}
