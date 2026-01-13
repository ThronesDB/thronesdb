<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Serializable;

/**
 * @package App\Entity
 */
interface RarityInterface extends Serializable
{
    /**
     * @param int $id
     */
    public function setId(int $id): void;

    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @param string $code
     */
    public function setCode(string $code): void;

    /**
     * return string
     */
    public function setName(string $name): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @param CardInterface $card
     */
    public function addCard(CardInterface $card): void;

    /**
     * @param CardInterface $card
     */
    public function removeCard(CardInterface $card): void;

    /**
     * @return Collection
     */
    public function getCards(): Collection;
}
