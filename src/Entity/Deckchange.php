<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Deckchange
 *
 * @ORM\Table(name="deckchange")
 * @ORM\Entity
 */
class Deckchange
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected $dateCreation;

    /**
     * @var string
     *
     * @ORM\Column(name="variation", type="string", length=1024)
     */
    protected $variation;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_saved", type="boolean")
     */
    protected $isSaved;

    /**
     * @var string|null
     *
     * @ORM\Column(name="version", type="string", length=8, nullable=true)
     */
    protected $version;

    /**
     * @var Deck
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="changes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deck_id", referencedColumnName="id")
     * })
     */
    protected $deck;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set dateCreation
     *
     * @param DateTime $dateCreation
     *
     * @return Deckchange
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set variation
     *
     * @param string $variation
     *
     * @return Deckchange
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;

        return $this;
    }

    /**
     * Get variation
     *
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * Set isSaved
     *
     * @param bool $isSaved
     *
     * @return Deckchange
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;

        return $this;
    }

    /**
     * Get isSaved
     *
     * @return bool
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * Set deck
     *
     * @param Deck $deck
     *
     * @return Deckchange
     */
    public function setDeck(Deck $deck = null)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * Get deck
     *
     * @return Deck
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * Set version
     *
     * @param string $version
     *
     * @return Deckchange
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
