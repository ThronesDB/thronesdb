<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use JsonSerializable;
use Ramsey\Uuid\UuidInterface;

interface DeckInterface extends CommonDeckInterface, JsonSerializable
{
    /**
     * @param string $problem
     */
    public function setProblem($problem);

    /**
     * @return string
     */
    public function getProblem();

    /**
     * @param string $tags
     */
    public function setTags($tags);

    /**
     * @return string
     */
    public function getTags();

    /**
     * @param Deckslot $slot
     */
    public function addSlot(Deckslot $slot);

    /**
     * @param Deckslot $slot
     */
    public function removeSlot(Deckslot $slot);

    /**
     * @param Decklist $child
     */
    public function addChild(Decklist $child);

    /**
     * @param Decklist $child
     */
    public function removeChild(Decklist $child);

    /**
     * @return Collection
     */
    public function getChildren();

    /**
     * @param DeckchangeInterface $change
     */
    public function addChange(DeckchangeInterface $change);

    /**
     * @param DeckchangeInterface $change
     */
    public function removeChange(DeckchangeInterface $change);

    /**
     * @return Collection
     */
    public function getChanges();

    /**
     * @param Pack $lastPack
     */
    public function setLastPack(Pack $lastPack = null);

    /**
     * @return Pack
     */
    public function getLastPack();

    /**
     * @param Decklist $parent
     */
    public function setParent(Decklist $parent = null);

    /**
     * @return Decklist
     */
    public function getParent();

    /**
     * @param int $majorVersion
     */
    public function setMajorVersion($majorVersion);

    /**
     * @return int
     */
    public function getMajorVersion();

    /**
     * @param int $minorVersion
     */
    public function setMinorVersion($minorVersion);

    /**
     * @return int
     */
    public function getMinorVersion();

    /**
     * @return array
     */
    public function getHistory();

    /**
     * @return bool
     */
    public function getIsUnsaved();

    /**
     * @param UuidInterface $uuid
     */
    public function setUuid($uuid);

    /**
     * @return UuidInterface|null
     */
    public function getUuid();
}
