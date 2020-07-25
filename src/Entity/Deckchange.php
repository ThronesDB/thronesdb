<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="deckchange")
 * @ORM\Entity
 * @package App\Entity
 */
class Deckchange implements DeckchangeInterface
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
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * @inheritdoc
     */
    public function setVariation($variation)
    {
        $this->variation = $variation;
    }

    /**
     * @inheritdoc
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @inheritdoc
     */
    public function setIsSaved($isSaved)
    {
        $this->isSaved = $isSaved;
    }

    /**
     * @inheritdoc
     */
    public function getIsSaved()
    {
        return $this->isSaved;
    }

    /**
     * @inheritdoc
     */
    public function setDeck(DeckInterface $deck = null)
    {
        $this->deck = $deck;
    }

    /**
     * @inheritdoc
     */
    public function getDeck()
    {
        return $this->deck;
    }

    /**
     * @inheritdoc
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @inheritdoc
     */
    public function getVersion()
    {
        return $this->version;
    }
}
