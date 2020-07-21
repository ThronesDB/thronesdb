<?php

namespace App\Model;

use App\Entity\CardInterface;

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
