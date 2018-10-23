<?php

namespace AppBundle\Model;

/**
 * Interface for a collection of SlotInterface
 */
interface SlotCollectionInterface extends \Countable, \IteratorAggregate, \ArrayAccess
{
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
    public function getSlotsByCycleOrder($deluxeAfter);
    
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
}
