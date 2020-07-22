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
     * @var DeckInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Deck", inversedBy="changes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deck_id", referencedColumnName="id")
     * })
     */
    protected $deck;

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @return DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @param string $variation
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    /**
     * @return string
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @param bool $isSaved
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;
    }

    /**
     * @return bool
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * @param DeckInterface $deck
     */
    public function setDeck(DeckInterface $deck = null)
    {
        $this->deck = $deck;
    }

    /**
     * @return DeckInterface
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
