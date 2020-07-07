<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity
 */
class Comment
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
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="date_creation", type="datetime", nullable=false)
     */
    protected $dateCreation;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_hidden", type="boolean")
     */
    protected $isHidden;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    protected $user;

    /**
     * @var Decklist
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Decklist", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="decklist_id", referencedColumnName="id")
     * })
     */
    protected $decklist;

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
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
     * @param bool $isHidden
     */
    public function setIsHidden($isHidden)
    {
        $this->isHidden = $isHidden;
    }

    /**
     * @return bool
     */
    public function getIsHidden()
    {
        return $this->isHidden;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Decklist $decklist
     */
    public function setDecklist(Decklist $decklist = null)
    {
        $this->decklist = $decklist;
    }

    /**
     * @return Decklist
     */
    public function getDecklist()
    {
        return $this->decklist;
    }
}
