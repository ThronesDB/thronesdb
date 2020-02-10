<?php

namespace AppBundle\Classes;

use AppBundle\Entity\Card;

/**
 * Banned List Checker.
 *
 * Class BannedList
 * @package AppBundle\Classes
 */
class BannedListChecker
{
    const BANNED_CARDS = [
        "16001",
        "16002",
        "16003",
        "16004",
        "16005",
        "16006",
        "16007",
        "16008",
        "16009",
        "16010",
        "16011",
        "16012",
        "16013",
        "16014",
        "16015",
        "16016",
        "16017",
        "16018",
        "16019",
        "16020",
        "16021",
        "16022",
        "16023",
        "16024",
        "16025",
        "16026",
        "16027",
        "16028",
        "16029",
        "16030",
        "16031",
        "16032",
        "16033",
        "16034",
        "16035",
        "16036",
    ];

    /**
     * @param array $cardCodes
     * @return bool
     */
    public function isLegal(array $cardCodes)
    {
        return empty(array_intersect($cardCodes, self::BANNED_CARDS));
    }
}
