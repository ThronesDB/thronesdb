<?php

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Serializable;

/**
 * Interface CardInterface
 * @package App\Entity
 */
interface CardInterface extends Serializable
{
    /**
     * @param int $id
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $position
     */
    public function setPosition($position);

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @param string $code
     */
    public function setCode($code);

    /**
     * @return string
     */
    public function getCode();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $cost
     */
    public function setCost($cost);

    /**
     * @return string
     */
    public function getCost();

    /**
     * @param string $text
     */
    public function setText($text);

    /**
     * @return string
     */
    public function getText();

    /**
     * @param DateTime $dateCreation
     */
    public function setDateCreation($dateCreation);

    /**
     * @return DateTime
     */
    public function getDateCreation();

    /**
     * @param DateTime $dateUpdate
     */
    public function setDateUpdate($dateUpdate);

    /**
     * @return DateTime
     */
    public function getDateUpdate();

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity);

    /**
     * @return int
     */
    public function getQuantity();

    /**
     * @param int $income
     */
    public function setIncome($income);

    /**
     * @return int
     */
    public function getIncome();

    /**
     * @param int $initiative
     */
    public function setInitiative($initiative);

    /**
     * @return int
     */
    public function getInitiative();

    /**
     * @param int $claim
     */
    public function setClaim($claim);

    /**
     * @return int
     */
    public function getClaim();

    /**
     * @param int $reserve
     */
    public function setReserve($reserve);

    /**
     * @return int
     */
    public function getReserve();

    /**
     * @param int $deckLimit
     */
    public function setDeckLimit($deckLimit);

    /**
     * @return int
     */
    public function getDeckLimit();

    /**
     * @param int $strength
     */
    public function setStrength($strength);

    /**
     * @return int
     */
    public function getStrength();

    /**
     * @param string $traits
     */
    public function setTraits($traits);

    /**
     * @return string
     */
    public function getTraits();

    /**
     * @param string $flavor
     */
    public function setFlavor($flavor);

    /**
     * @return string
     */
    public function getFlavor();

    /**
     * @param string $illustrator
     */
    public function setIllustrator($illustrator);

    /**
     * @return string
     */
    public function getIllustrator();

    /**
     * @param bool $isUnique
     */
    public function setIsUnique($isUnique);

    /**
     * @return bool
     */
    public function getIsUnique();

    /**
     * @param bool $isLoyal
     */
    public function setIsLoyal($isLoyal);

    /**
     * @return bool
     */
    public function getIsLoyal();

    /**
     * @param bool $isMilitary
     */
    public function setIsMilitary($isMilitary);

    /**
     * @return bool
     */
    public function getIsMilitary();

    /**
     * @param bool $isIntrigue
     */
    public function setIsIntrigue($isIntrigue);

    /**
     * @return bool
     */
    public function getIsIntrigue();

    /**
     * @param bool $isPower
     */
    public function setIsPower($isPower);

    /**
     * @return bool
     */
    public function getIsPower();

    /**
     * @param bool $octgnId
     */
    public function setOctgnId($octgnId);

    /**
     * @return bool
     */
    public function getOctgnId();

    /**
     * @param ReviewInterface $review
     */
    public function addReview(ReviewInterface $review);

    /**
     * @param ReviewInterface $review
     */
    public function removeReview(ReviewInterface $review);

    /**
     * @return Collection
     */
    public function getReviews();

    /**
     * @param PackInterface $pack
     */
    public function setPack(PackInterface $pack = null);

    /**
     * @return PackInterface
     */
    public function getPack();

    /**
     * @param TypeInterface $type
     */
    public function setType(TypeInterface $type = null);

    /**
     * @return TypeInterface
     */
    public function getType();

    /**
     * @param FactionInterface $faction
     */
    public function setFaction(FactionInterface $faction = null);

    /**
     * @return FactionInterface
     */
    public function getFaction();

    /**
     * @return int
     */
    public function getCostIncome();

    /**
     * @return int
     */
    public function getStrengthInitiative();

    /**
     * @param string $designer
     */
    public function setDesigner($designer);

    /**
     * @return string
     */
    public function getDesigner();

    /**
     * @return bool
     */
    public function getIsMultiple(): bool;

    /**
     * @param bool $isMultiple
     */
    public function setIsMultiple(bool $isMultiple);

    /**
     * @return string|null
     */
    public function getImageUrl();

    /**
     * @param string|null $imageUrl
     */
    public function setImageUrl(string $imageUrl = null);

    /**
     * Checks if this card has the "Shadow" keyword.
     * @param string $shadow The keyword "Shadow" in whatever language.
     * @return bool
     */
    public function hasShadowKeyword($shadow): bool;
}
