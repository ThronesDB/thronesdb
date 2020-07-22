<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Serializable;

/**
 * @package App\Entity
 */
interface TypeInterface extends Serializable
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
     * @param string $code
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param CardInterface $card
     */
    public function addCard(CardInterface $card);

    /**
     * @param CardInterface $card
     */
    public function removeCard(CardInterface $card);

    /**
     * @return Collection
     */
    public function getCards();
}
