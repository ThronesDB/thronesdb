<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User extends BaseUser implements UserInterface
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
     * @var int
     *
     * @ORM\Column(name="reputation", type="integer", nullable=false)
     */
    protected $reputation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="resume", type="text", nullable=true)
     */
    protected $resume;

    /**
     * @var string|null
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    protected $color;

    /**
     * @var int
     *
     * @ORM\Column(name="donation", type="integer", nullable=false)
     */
    protected $donation;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_notif_author", type="boolean", nullable=false, options={"default"="1"})
     */
    protected $isNotifAuthor = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_notif_commenter", type="boolean", nullable=false, options={"default"="1"})
     */
    protected $isNotifCommenter = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_notif_mention", type="boolean", nullable=false, options={"default"="1"})
     */
    protected $isNotifMention = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_notif_follow", type="boolean", nullable=false, options={"default"="1"})
     */
    protected $isNotifFollow = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_notif_successor", type="boolean", nullable=false, options={"default"="1"})
     */
    protected $isNotifSuccessor = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_share_decks", type="boolean", nullable=false, options={"default"="0"})
     */
    protected $isShareDecks = false;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Deck", mappedBy="user", cascade={"remove"})
     * @ORM\OrderBy({
     *     "dateUpdate"="DESC"
     * })
     */
    protected $decks;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Decklist", mappedBy="user")
     */
    protected $decklists;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="user")
     * @ORM\OrderBy({
     *     "dateCreation"="DESC"
     * })
     */
    protected $comments;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Review", mappedBy="user")
     * @ORM\OrderBy({
     *     "dateCreation"="DESC"
     * })
     */
    protected $reviews;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Decklist", mappedBy="favorites", cascade={"remove"})
     */
    protected $favorites;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Decklist", mappedBy="votes", cascade={"remove"})
     */
    protected $votes;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Review", mappedBy="votes", cascade={"remove"})
     */
    protected $reviewvotes;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="followers")
     */
    protected $following;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\User", inversedBy="following")
     * @ORM\JoinTable(name="follow",
     *   joinColumns={
     *     @ORM\JoinColumn(name="following_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="follower_id", referencedColumnName="id")
     *   }
     * )
     */
    protected $followers;

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->reputation = 1;
        $this->donation = 0;
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
    public function setReputation($reputation)
    {
        $this->reputation = $reputation;
    }

    /**
     * @inheritdoc
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * @inheritdoc
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    }

    /**
     * @inheritdoc
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @inheritdoc
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @inheritdoc
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @inheritdoc
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    }

    /**
     * @inheritdoc
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * @inheritdoc
     */
    public function setIsNotifAuthor($isNotifAuthor)
    {
        $this->isNotifAuthor = $isNotifAuthor;
    }

    /**
     * @inheritdoc
     */
    public function getIsNotifAuthor()
    {
        return $this->isNotifAuthor;
    }

    /**
     * @inheritdoc
     */
    public function setIsNotifCommenter($isNotifCommenter)
    {
        $this->isNotifCommenter = $isNotifCommenter;
    }

    /**
     * @inheritdoc
     */
    public function getIsNotifCommenter()
    {
        return $this->isNotifCommenter;
    }

    /**
     * @inheritdoc
     */
    public function setIsNotifMention($isNotifMention)
    {
        $this->isNotifMention = $isNotifMention;
    }

    /**
     * @inheritdoc
     */
    public function getIsNotifMention()
    {
        return $this->isNotifMention;
    }

    /**
     * @inheritdoc
     */
    public function setIsNotifFollow($isNotifFollow)
    {
        $this->isNotifFollow = $isNotifFollow;
    }

    /**
     * @inheritdoc
     */
    public function getIsNotifFollow()
    {
        return $this->isNotifFollow;
    }

    /**
     * @inheritdoc
     */
    public function setIsNotifSuccessor($isNotifSuccessor)
    {
        $this->isNotifSuccessor = $isNotifSuccessor;
    }

    /**
     * @inheritdoc
     */
    public function getIsNotifSuccessor()
    {
        return $this->isNotifSuccessor;
    }

    /**
     * @inheritdoc
     */
    public function setIsShareDecks($isShareDecks)
    {
        $this->isShareDecks = $isShareDecks;
    }

    /**
     * @inheritdoc
     */
    public function getIsShareDecks()
    {
        return $this->isShareDecks;
    }

    /**
     * @inheritdoc
     */
    public function addDeck(DeckInterface $deck)
    {
        $this->decks->add($deck);
    }

    /**
     * @inheritdoc
     */
    public function removeDeck(DeckInterface $deck)
    {
        $this->decks->removeElement($deck);
    }

    /**
     * @inheritdoc
     */
    public function getDecks()
    {
        return $this->decks;
    }

    /**
     * @inheritdoc
     */
    public function addDecklist(DecklistInterface $decklist)
    {
        $this->decklists->add($decklist);
    }

    /**
     * @inheritdoc
     */
    public function removeDecklist(DecklistInterface $decklist)
    {
        $this->decklists->removeElement($decklist);
    }

    /**
     * @inheritdoc
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * @inheritdoc
     */
    public function addComment(CommentInterface $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @inheritdoc
     */
    public function removeComment(CommentInterface $comment)
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
    public function addReview(ReviewInterface $review)
    {
        $this->reviews->add($review);
    }

    /**
     * @inheritdoc
     */
    public function removeReview(ReviewInterface $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * @inheritdoc
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @inheritdoc
     */
    public function addFavorite(DecklistInterface $favorite)
    {
        $favorite->addFavorite($this);
        $this->favorites->add($favorite);
    }

    /**
     * @inheritdoc
     */
    public function removeFavorite(DecklistInterface $favorite)
    {
        $favorite->removeFavorite($this);
        $this->favorites->removeElement($favorite);
    }

    /**
     * @inheritdoc
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * @inheritdoc
     */
    public function addVote(DecklistInterface $vote)
    {
        $vote->addVote($this);
        $this->votes->add($vote);
    }

    /**
     * @inheritdoc
     */
    public function removeVote(DecklistInterface $vote)
    {
        $vote->removeVote($this);
        $this->votes->removeElement($vote);
    }

    /**
     * @inheritdoc
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @inheritdoc
     */
    public function addReviewvote(ReviewInterface $reviewvote)
    {
        $this->reviewvotes->add($reviewvote);
    }

    /**
     * @inheritdoc
     */
    public function removeReviewvote(ReviewInterface $reviewvote)
    {
        $this->reviewvotes->removeElement($reviewvote);
    }

    /**
     * @inheritdoc
     */
    public function getReviewvotes()
    {
        return $this->reviewvotes;
    }

    /**
     * @inheritdoc
     */
    public function addFollowing(UserInterface $following)
    {
        $this->following->add($following);
    }

    /**
     * @inheritdoc
     */
    public function removeFollowing(UserInterface $following)
    {
        $this->following->removeElement($following);
    }

    /**
     * @inheritdoc
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @inheritdoc
     */
    public function addFollower(UserInterface $follower)
    {
        $this->followers->add($follower);
    }

    /**
     * @inheritdoc
     */
    public function removeFollower(UserInterface $follower)
    {
        $this->followers->removeElement($follower);
    }

    /**
     * @inheritdoc
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @inheritdoc
     */
    public function getMaxNbDecks()
    {
        return 2 * (100 + floor($this->reputation / 10)) + 100;
    }
}
