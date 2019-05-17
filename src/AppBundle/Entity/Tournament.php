<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Tournament
 */
class Tournament
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Collection
     */
    private $decklists;

    /**
     * @var boolean
     */
    protected $active;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->decklists = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Tournament
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active) : void
    {
        $this->active = $active;
    }

    /**
     * Add decklist
     *
     * @param Decklist $decklist
     *
     * @return Tournament
     */
    public function addDecklist(Decklist $decklist)
    {
        $this->decklists[] = $decklist;

        return $this;
    }

    /**
     * Remove decklist
     *
     * @param Decklist $decklist
     */
    public function removeDecklist(Decklist $decklist)
    {
        $this->decklists->removeElement($decklist);
    }

    /**
     * Get decklists
     *
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }
}
