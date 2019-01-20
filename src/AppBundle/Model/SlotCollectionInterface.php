<?php

namespace AppBundle\Model;

use Doctrine\Common\Collections\Collection;

/**
 * Interface for a collection of SlotInterface
 */
interface SlotCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * Adds a slot to the collection.
     * @param SlotInterface $slot
     */
    public function add(SlotInterface $slot);

    /**
     * Removes a slot from the collection.
     * @param SlotInterface $slot
     */
    public function removeElement(SlotInterface $slot);
    /**
     * Get quantity of cards
     * @return integer
     */
    public function countCards();
    
    /**
     * Get included packs
     * @return \AppBundle\Entity\Pack[]
     */
    public function getIncludedPacks();
    
    /**
     * Get all slots sorted by type code (including plots)
     * @return array
     */
    public function getSlotsByType();    

    /**
     * Get all slots sorted by cycle number (including plots)
     * @return array
     */
    public function getSlotsByCycleOrder();
    
    /**
     * Get all slot counts sorted by type code (excluding plots)
     * @return array
     */
    public function getCountByType();
    
    /**
     * Get the plot deck
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getPlotDeck();

    /**
     * Get all the agendas
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getAgendas();
    
    /**
     * Return true is agenda is Alliance (06018)
     * @return boolean
     */
    public function isAlliance();

    /**
     * Get the draw deck
     * @return \AppBundle\Model\SlotCollectionInterface
     */
    public function getDrawDeck();
    
    /**
     * Get the content as an array card_code => qty
     * @return array
     */
    public function getContent();
    
    /**
     *
     * @param string $faction_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByFaction($faction_code);
    
    /**
     *
     * @param string $type_code
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByType($type_code);
    
    /**
     *
     * @param string $trait
     * @return \AppBundle\Model\SlotCollectionDecorator
     */
    public function filterByTrait($trait);

    /**
     * Checks Restricted List compliance for the Melee format.
     * @return boolean
     */
    public function isLegalForMelee();

    /**
     * Checks Restricted List compliance for the Joust format.
     * @return boolean
     */
    public function isLegalforJoust();

    /**
     * Returns the collection of slots.
     * @return Collection
     */
    public function getSlots();

    /**
     * Returns a map of limits and total copies per card in the collection, keyed off by card name.
     * @return array
     */
    public function getCopiesAndDeckLimit();
}
