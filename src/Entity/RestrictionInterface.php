<?php

namespace App\Entity;

use DateTime;

/**
 * Interface RestrictionInterface
 * @package App\Entity
 */
interface RestrictionInterface
{
    /**
     * @param string $code
     */
    public function setCode(string $code): void;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param DateTime $effectiveOn
     */
    public function setEffectiveOn(DateTime $effectiveOn): void;

    /**
     * @return DateTime
     */
    public function getEffectiveOn(): DateTime;

    /**
     * @param string
     */
    public function setIssuer(string $issuer): void;

    /**
     * @return string
     */
    public function getIssuer(): string;

    /**
     * @param string $cardSet
     */
    public function setCardSet(string $cardSet): void;

    /**
     * @return string
     */
    public function getCardSet(): string;

    /**
     * @param array $contents
     */
    public function setContents(array $contents): void;

    /**
     * @return array
     */
    public function getContents(): array;

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @param string $version
     */
    public function setVersion(string $version): void;

    /**
     * @return string
     */
    public function getVersion(): string;
}
