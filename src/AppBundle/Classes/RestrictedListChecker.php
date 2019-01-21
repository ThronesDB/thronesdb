<?php

namespace AppBundle\Classes;

use AppBundle\Entity\Decklistslot;
use Doctrine\Common\Collections\Collection;

/**
 * Checks if a given list of cards is legal for tournament play in the Joust and Melee formats.
 * @package AppBundle\Classes
 */
class RestrictedListChecker
{
    /**
     * @var array
     */
    const JOUST_RESTRICTED_CARDS = [
        "01100",
        "01109",
        "03038",
        "04001",
        "04017",
        "05010",
        "05049",
        "06004",
        "06039",
        "06040",
        "06098",
        "06100",
        "06103",
        "09001",
        "09017",
        "09023",
        "10045",
        "10050"
    ];

    /**
     * @var array
     */
    const MELEE_RESTRICTED_CARDS = [
        "01001",
        "01013",
        "01043",
        "01078",
        "01119",
        "01162",
        "02012",
        "02024",
        "02060",
        "03003",
        "04003",
        "04118",
        "05001",
        "05010",
        "06004",
        "06039",
        "06098",
        "07036",
        "08098",
        "09028"
    ];

    /**
     * @param array $cardCodes
     * @return bool
     */
    public function isLegalForMelee(array $cardCodes)
    {
        return $this->isLegal($cardCodes, self::MELEE_RESTRICTED_CARDS);
    }

    /**
     * @param array $cardCodes
     * @return bool
     */
    public function isLegalForJoust(array $cardCodes)
    {
        return $this->isLegal($cardCodes, self::JOUST_RESTRICTED_CARDS);
    }

    /**
     * @param array $cardCodes
     * @param array $restrictedList
     * @return bool
     */
    protected function isLegal(array $cardCodes, array $restrictedList)
    {
        $intersection = array_intersect($cardCodes, $restrictedList);
        return 2 > count($intersection);
    }
}
