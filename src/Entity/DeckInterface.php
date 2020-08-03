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
     * @param DeckslotInterface $slot
     */
    public function addSlot(DeckslotInterface $slot);

    /**
     * @param DeckslotInterface $slot
     */
    public function removeSlot(DeckslotInterface $slot);

    /**
     * @param DecklistInterface $child
     */
    public function addChild(DecklistInterface $child);

    /**
     * @param DecklistInterface $child
     */
    public function removeChild(DecklistInterface $child);

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
     * @param PackInterface $lastPack
     */
    public function setLastPack(PackInterface $lastPack = null);

    /**
     * @return PackInterface
     */
    public function getLastPack();

    /**
     * @param DecklistInterface|null $parent
     */
    public function setParent(DecklistInterface $parent = null);

    /**
     * @return DecklistInterface
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
