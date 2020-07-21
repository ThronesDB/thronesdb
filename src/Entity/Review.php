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
     * @var CardInterface
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Card", inversedBy="reviews")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_id", referencedColumnName="id")
     * })
     */
    protected $card;

    /**
     * @var UserInterface
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
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @param string $textMd
     */
    public function setTextMd($textMd)
    {
        $this->textMd = $textMd;
    }

    /**
     * @return string
     */
    public function getTextMd()
    {
        return $this->textMd;
    }

    /**
     * @param string $textHtml
     */
    public function setTextHtml($textHtml)
    {
        $this->textHtml = $textHtml;
    }

    /**
     * @return string
     */
    public function getTextHtml()
    {
        return $this->textHtml;
    }

    /**
     * @param int $nbVotes
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;
    }

    /**
     * @return int
     */
    public function getNbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * @param Reviewcomment $comment
     */
    public function addComment(Reviewcomment $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @param Reviewcomment $comment
     */
    public function removeComment(Reviewcomment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * @return Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param CardInterface $card
     */
    public function setCard(CardInterface $card = null)
    {
        $this->card = $card;
    }

    /**
     * @return CardInterface
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param UserInterface $vote
     */
    public function addVote(UserInterface $vote)
    {
        $this->votes->add($vote);
    }

    /**
     * @param UserInterface $vote
     */
    public function removeVote(UserInterface $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }
}
