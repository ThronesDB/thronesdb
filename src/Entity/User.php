<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 */
class User extends BaseUser
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

    public function __construct()
    {
        parent::__construct();

        $this->reputation = 1;
        $this->donation = 0;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param int $reputation
     */
    public function setReputation($reputation)
    {
        $this->reputation = $reputation;
    }

    /**
     * @return int
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * @param string $resume
     */
    public function setResume($resume)
    {
        $this->resume = $resume;
    }

    /**
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param int $donation
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;
    }

    /**
     * @return int
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * @param bool $isNotifAuthor
     */
    public function setIsNotifAuthor($isNotifAuthor)
    {
        $this->isNotifAuthor = $isNotifAuthor;
    }

    /**
     * @return bool
     */
    public function getIsNotifAuthor()
    {
        return $this->isNotifAuthor;
    }

    /**
     * @param bool $isNotifCommenter
     */
    public function setIsNotifCommenter($isNotifCommenter)
    {
        $this->isNotifCommenter = $isNotifCommenter;
    }

    /**
     * @return bool
     */
    public function getIsNotifCommenter()
    {
        return $this->isNotifCommenter;
    }

    /**
     * @param bool $isNotifMention
     */
    public function setIsNotifMention($isNotifMention)
    {
        $this->isNotifMention = $isNotifMention;
    }

    /**
     * @return bool
     */
    public function getIsNotifMention()
    {
        return $this->isNotifMention;
    }

    /**
     * @param bool $isNotifFollow
     */
    public function setIsNotifFollow($isNotifFollow)
    {
        $this->isNotifFollow = $isNotifFollow;
    }

    /**
     * @return bool
     */
    public function getIsNotifFollow()
    {
        return $this->isNotifFollow;
    }

    /**
     * @param bool $isNotifSuccessor
     */
    public function setIsNotifSuccessor($isNotifSuccessor)
    {
        $this->isNotifSuccessor = $isNotifSuccessor;
    }

    /**
     * @return bool
     */
    public function getIsNotifSuccessor()
    {
        return $this->isNotifSuccessor;
    }

    /**
     * @param bool $isShareDecks
     */
    public function setIsShareDecks($isShareDecks)
    {
        $this->isShareDecks = $isShareDecks;
    }

    /**
     * @return bool
     */
    public function getIsShareDecks()
    {
        return $this->isShareDecks;
    }

    /**
     * @param Deck $deck
     */
    public function addDeck(Deck $deck)
    {
        $this->decks->add($deck);
    }

    /**
     * @param Deck $deck
     */
    public function removeDeck(Deck $deck)
    {
        $this->decks->removeElement($deck);
    }

    /**
     * @return Collection
     */
    public function getDecks()
    {
        return $this->decks;
    }

    /**
     * @param Decklist $decklist
     */
    public function addDecklist(Decklist $decklist)
    {
        $this->decklists->add($decklist);
    }

    /**
     * @param Decklist $decklist
     */
    public function removeDecklist(Decklist $decklist)
    {
        $this->decklists->removeElement($decklist);
    }

    /**
     * @return Collection
     */
    public function getDecklists()
    {
        return $this->decklists;
    }

    /**
     * @param Comment $comment
     */
    public function addComment(Comment $comment)
    {
        $this->comments->add($comment);
    }

    /**
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
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
     * @param Review $review
     */
    public function addReview(Review $review)
    {
        $this->reviews->add($review);
    }

    /**
     * @param Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * @param Decklist $favorite
     */
    public function addFavorite(Decklist $favorite)
    {
        $favorite->addFavorite($this);
        $this->favorites->add($favorite);
    }

    /**
     * @param Decklist $favorite
     */
    public function removeFavorite(Decklist $favorite)
    {
        $favorite->removeFavorite($this);
        $this->favorites->removeElement($favorite);
    }

    /**
     * @return Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * @param Decklist $vote
     */
    public function addVote(Decklist $vote)
    {
        $vote->addVote($this);
        $this->votes->add($vote);
    }

    /**
     * @param Decklist $vote
     */
    public function removeVote(Decklist $vote)
    {
        $vote->removeVote($this);
        $this->votes->removeElement($vote);
    }

    /**
     * @return Collection
     */
    public function getVotes()
    {
        return $this->votes;
    }

    /**
     * @param Review $reviewvote
     */
    public function addReviewvote(Review $reviewvote)
    {
        $this->reviewvotes->add($reviewvote);
    }

    /**
     * @param Review $reviewvote
     */
    public function removeReviewvote(Review $reviewvote)
    {
        $this->reviewvotes->removeElement($reviewvote);
    }

    /**
     * @return Collection
     */
    public function getReviewvotes()
    {
        return $this->reviewvotes;
    }

    /**
     * @param User $following
     */
    public function addFollowing(User $following)
    {
        $this->following->add($following);
    }

    /**
     * @param User $following
     */
    public function removeFollowing(User $following)
    {
        $this->following->removeElement($following);
    }

    /**
     * @return Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * @param User $follower
     */
    public function addFollower(User $follower)
    {
        $this->followers->add($follower);
    }

    /**
     * @param User $follower
     */
    public function removeFollower(User $follower)
    {
        $this->followers->removeElement($follower);
    }

    /**
     * @return Collection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @return float|int
     */
    public function getMaxNbDecks()
    {
        return 2*(100+floor($this->reputation/ 10));
    }
}
