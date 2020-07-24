<?php

namespace App\Entity;

/**
 * Interface for an entity with a Card and a Quantity
 */
interface SlotInterface
{

    /**
     * Get card
     * @return CardInterface
     */
    public function getCard();

    /**
     * Get quantity
     * @return integer
     */
    public function getQuantity();
}
