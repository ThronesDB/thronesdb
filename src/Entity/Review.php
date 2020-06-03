<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Review
 *
 * @ORM\Table(name="review")
 * @ORM\Entity
 */
class Review
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
     * @var DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="date_update", type="datetime", nullable=false)
     */
    protected $dateUpdate;

    /**
     * @var string
     *
     * @ORM\Column(name="text_md", type="text", nullable=false)
     */
    protected $textMd;

    /**
     * @var string
     *
     * @ORM\Column(name="text_html", type="text", nullable=false)
     */
    protected $textHtml;

    /**
     * @var int
     *
     * @ORM\Column(name="nb_votes", type="smallint", nullable=false)
     */
    protected $nbVotes;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Reviewcomment", mappedBy="review", cascade={"persist"})
     */
    protected $comments;

    /**
     * @var Card
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Card", inversedBy="reviews")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     * })
     */
    protected $card;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="reviews")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="reviewvotes", cascade={"persist"})
     * @ORM\JoinTable(name="reviewvote",
     *   joinColumns={
     *     @ORM\JoinColumn(name="review_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $votes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

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
     * @return Review
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
     * Set dateUpdate
     *
     * @param DateTime $dateUpdate
     *
     * @return Review
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;

        return $this;
    }

    /**
     * Get dateUpdate
     *
     * @return DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * Set textMd
     *
     * @param string $textMd
     *
     * @return Review
     */
    public function setTextMd($textMd)
    {
        $this->textMd = $textMd;

        return $this;
    }

    /**
     * Get textMd
     *
     * @return string
     */
    public function getTextMd()
    {
        return $this->textMd;
    }

    /**
     * Set textHtml
     *
     * @param string $textHtml
     *
     * @return Review
     */
    public function setTextHtml($textHtml)
    {
        $this->textHtml = $textHtml;

        return $this;
    }

    /**
     * Get textHtml
     *
     * @return string
     */
    public function getTextHtml()
    {
        return $this->textHtml;
    }

    /**
     * Set nbVotes
     *
     * @param int $nbVotes
     *
     * @return Review
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;

        return $this;
    }

    /**
     * Get nbVotes
     *
     * @return int
     */
    public function getNbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * Add comment
     *
     * @param \App\Entity\Reviewcomment $comment
     *
     * @return Review
     */
    public function addComment(\App\Entity\Reviewcomment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \App\Entity\Reviewcomment $comment
     */
    public function removeComment(\App\Entity\Reviewcomment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set card
     *
     * @param Card $card
     *
     * @return Review
     */
    public function setCard(Card $card = null)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get card
     *
     * @return Card
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Review
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add vote
     *
     * @param User $vote
     *
     * @return Review
     */
    public function addVote(User $vote)
    {
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param User $vote
     */
    public function removeVote(User $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * Get votes
     *
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }
}
