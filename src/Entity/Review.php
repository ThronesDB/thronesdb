<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="review")
 * @ORM\Entity
 * @package App\Entity
 */
class Review implements ReviewInterface
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
    public function setDateUpdate($dateUpdate)
    {
        $this->dateUpdate = $dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function getDateUpdate()
    {
        return $this->dateUpdate;
    }

    /**
     * @inheritdoc
     */
    public function setTextMd($textMd)
    {
        $this->textMd = $textMd;
    }

    /**
     * @inheritdoc
     */
    public function getTextMd()
    {
        return $this->textMd;
    }

    /**
     * @inheritdoc
     */
    public function setTextHtml($textHtml)
    {
        $this->textHtml = $textHtml;
    }

    /**
     * @inheritdoc
     */
    public function getTextHtml()
    {
        return $this->textHtml;
    }

    /**
     * @inheritdoc
     */
    public function setNbVotes($nbVotes)
    {
        $this->nbVotes = $nbVotes;
    }

    /**
     * @inheritdoc
     */
    public function getNbVotes()
    {
        return $this->nbVotes;
    }

    /**
     * @inheritdoc
     */
    public function addComment(ReviewcommentInterface $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @inheritdoc
     */
    public function removeComment(ReviewcommentInterface $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * @inheritdoc
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @inheritdoc
     */
    public function setCard(CardInterface $card = null)
    {
        $this->card = $card;
    }

    /**
     * @inheritdoc
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @inheritdoc
     */
    public function setUser(UserInterface $user = null)
    {
        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function addVote(UserInterface $vote)
    {
        $this->votes->add($vote);
    }

    /**
     * @inheritdoc
     */
    public function removeVote(UserInterface $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * @inheritdoc
     */
    public function getVotes()
    {
        return $this->votes;
    }
}
