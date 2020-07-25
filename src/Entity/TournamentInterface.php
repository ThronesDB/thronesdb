<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * @package App\Entity
 */
interface TournamentInterface
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
     * @param string $description
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void;

    /**
     * @param DecklistInterface $decklist
     */
    public function addDecklist(DecklistInterface $decklist);

    /**
     * @param DecklistInterface $decklist
     */
    public function removeDecklist(DecklistInterface $decklist);

    /**
     * @return Collection
     */
    public function getDecklists();
}
