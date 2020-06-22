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
     * Set dateCreation
     *
     * @param DateTime $dateCreation
     *
     * @return User
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
     * @return User
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
     * Set reputation
     *
     * @param int $reputation
     *
     * @return User
     */
    public function setReputation($reputation)
    {
        $this->reputation = $reputation;

        return $this;
    }

    /**
     * Get reputation
     *
     * @return int
     */
    public function getReputation()
    {
        return $this->reputation;
    }

    /**
     * Set resume
     *
     * @param string $resume
     *
     * @return User
     */
    public function setResume($resume)
    {
        $this->resume = $resume;

        return $this;
    }

    /**
     * Get resume
     *
     * @return string
     */
    public function getResume()
    {
        return $this->resume;
    }

    /**
     * Set color
     *
     * @param string $color
     *
     * @return User
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set donation
     *
     * @param int $donation
     *
     * @return User
     */
    public function setDonation($donation)
    {
        $this->donation = $donation;

        return $this;
    }

    /**
     * Get donation
     *
     * @return int
     */
    public function getDonation()
    {
        return $this->donation;
    }

    /**
     * Set isNotifAuthor
     *
     * @param bool $isNotifAuthor
     *
     * @return User
     */
    public function setIsNotifAuthor($isNotifAuthor)
    {
        $this->isNotifAuthor = $isNotifAuthor;

        return $this;
    }

    /**
     * Get isNotifAuthor
     *
     * @return bool
     */
    public function getIsNotifAuthor()
    {
        return $this->isNotifAuthor;
    }

    /**
     * Set isNotifCommenter
     *
     * @param bool $isNotifCommenter
     *
     * @return User
     */
    public function setIsNotifCommenter($isNotifCommenter)
    {
        $this->isNotifCommenter = $isNotifCommenter;

        return $this;
    }

    /**
     * Get isNotifCommenter
     *
     * @return bool
     */
    public function getIsNotifCommenter()
    {
        return $this->isNotifCommenter;
    }

    /**
     * Set isNotifMention
     *
     * @param bool $isNotifMention
     *
     * @return User
     */
    public function setIsNotifMention($isNotifMention)
    {
        $this->isNotifMention = $isNotifMention;

        return $this;
    }

    /**
     * Get isNotifMention
     *
     * @return bool
     */
    public function getIsNotifMention()
    {
        return $this->isNotifMention;
    }

    /**
     * Set isNotifFollow
     *
     * @param bool $isNotifFollow
     *
     * @return User
     */
    public function setIsNotifFollow($isNotifFollow)
    {
        $this->isNotifFollow = $isNotifFollow;

        return $this;
    }

    /**
     * Get isNotifFollow
     *
     * @return bool
     */
    public function getIsNotifFollow()
    {
        return $this->isNotifFollow;
    }

    /**
     * Set isNotifSuccessor
     *
     * @param bool $isNotifSuccessor
     *
     * @return User
     */
    public function setIsNotifSuccessor($isNotifSuccessor)
    {
        $this->isNotifSuccessor = $isNotifSuccessor;

        return $this;
    }

    /**
     * Get isNotifSuccessor
     *
     * @return bool
     */
    public function getIsNotifSuccessor()
    {
        return $this->isNotifSuccessor;
    }

    /**
     * Set isShareDecks
     *
     * @param bool $isShareDecks
     *
     * @return User
     */
    public function setIsShareDecks($isShareDecks)
    {
        $this->isShareDecks = $isShareDecks;

        return $this;
    }

    /**
     * Get isShareDecks
     *
     * @return bool
     */
    public function getIsShareDecks()
    {
        return $this->isShareDecks;
    }

    /**
     * Add deck
     *
     * @param Deck $deck
     *
     * @return User
     */
    public function addDeck(Deck $deck)
    {
        $this->decks[] = $deck;

        return $this;
    }

    /**
     * Remove deck
     *
     * @param Deck $deck
     */
    public function removeDeck(Deck $deck)
    {
        $this->decks->removeElement($deck);
    }

    /**
     * Get decks
     *
     * @return Collection
     */
    public function getDecks()
    {
        return $this->decks;
    }

    /**
     * Add decklist
     *
     * @param Decklist $decklist
     *
     * @return User
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

    /**
     * Add comment
     *
     * @param Comment $comment
     *
     * @return User
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param Comment $comment
     */
    public function removeComment(Comment $comment)
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
     * Add review
     *
     * @param Review $review
     *
     * @return User
     */
    public function addReview(Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review
     *
     * @param Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews
     *
     * @return Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Add favorite
     *
     * @param Decklist $favorite
     *
     * @return User
     */
    public function addFavorite(Decklist $favorite)
    {
        $favorite->addFavorite($this);
        $this->favorites[] = $favorite;

        return $this;
    }

    /**
     * Remove favorite
     *
     * @param Decklist $favorite
     */
    public function removeFavorite(Decklist $favorite)
    {
        $favorite->removeFavorite($this);
        $this->favorites->removeElement($favorite);
    }

    /**
     * Get favorites
     *
     * @return Collection
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * Add vote
     *
     * @param Decklist $vote
     *
     * @return User
     */
    public function addVote(Decklist $vote)
    {
        $vote->addVote($this);
        $this->votes[] = $vote;

        return $this;
    }

    /**
     * Remove vote
     *
     * @param Decklist $vote
     */
    public function removeVote(Decklist $vote)
    {
        $vote->removeVote($this);
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

    /**
     * Add reviewvote
     *
     * @param Review $reviewvote
     *
     * @return User
     */
    public function addReviewvote(Review $reviewvote)
    {
        $this->reviewvotes[] = $reviewvote;

        return $this;
    }

    /**
     * Remove reviewvote
     *
     * @param Review $reviewvote
     */
    public function removeReviewvote(Review $reviewvote)
    {
        $this->reviewvotes->removeElement($reviewvote);
    }

    /**
     * Get reviewvotes
     *
     * @return Collection
     */
    public function getReviewvotes()
    {
        return $this->reviewvotes;
    }

    /**
     * Add following
     *
     * @param User $following
     *
     * @return User
     */
    public function addFollowing(User $following)
    {
        $this->following[] = $following;

        return $this;
    }

    /**
     * Remove following
     *
     * @param User $following
     */
    public function removeFollowing(User $following)
    {
        $this->following->removeElement($following);
    }

    /**
     * Get following
     *
     * @return Collection
     */
    public function getFollowing()
    {
        return $this->following;
    }

    /**
     * Add follower
     *
     * @param User $follower
     *
     * @return User
     */
    public function addFollower(User $follower)
    {
        $this->followers[] = $follower;

        return $this;
    }

    /**
     * Remove follower
     *
     * @param User $follower
     */
    public function removeFollower(User $follower)
    {
        $this->followers->removeElement($follower);
    }

    /**
     * Get followers
     *
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
