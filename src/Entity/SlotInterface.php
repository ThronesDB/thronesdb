<?php

namespace App\Entity;

/**
 * Interface for an entity with a Card and a Quantity
 * @package App\Entity
 */
interface SlotInterface
{
    /**
     * @return CardInterface
     */
    public function getCard();

    /**
     * @param CardInterface $card
     */
    public function setCard(CardInterface $card);

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity);
}
