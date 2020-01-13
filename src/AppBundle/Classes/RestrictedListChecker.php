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
        "01109",
        "02091",
        "02092",
        "02102",
        "03038",
        "04001",
        "04017",
        "05010",
        "05049",
        "06004",
        "06011",
        "06038",
        "06039",
        "06040",
        "06063",
        "06098",
        "06100",
        "06103",
        "09001",
        "09017",
        "09023",
        "09051",
        "10045",
        "10048",
        "10050",
        "11021",
        "11033",
        "11034",
        "11044",
        "11051",
        "11061",
        "11076",
        "11082",
        "12002",
        "12029",
        "12045",
        "12046",
        "13044",
        "13085",
        "13086",
        "13103",
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
        "03038",
        "04003",
        "04118",
        "05001",
        "05010",
        "05049",
        "06004",
        "06011",
        "06039",
        "06040",
        "06098",
        "07036",
        "08098",
        "08120",
        "09028",
        "11054",
        "11076",
        "13107",
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
