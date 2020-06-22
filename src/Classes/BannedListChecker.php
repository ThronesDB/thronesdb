<?php

namespace App\Classes;

use App\Entity\Card;

/**
 * Banned List Checker.
 * The currently implemented BL were issued by The Conclave (v1.0), effective April 13th, 2020.
 *
 * Class BannedList
 * @package App\Classes
 */
class BannedListChecker
{
    const BANNED_CARDS = [
        "16001", // Ser Davos Seaworth (TTWDFL)
        "16002", // Melisandre's Favor (TTWDFL)
        "16003", // Wintertime Marauders (TTWDFL)
        "16004", // Conquer (TTWDFL)
        "16005", // Spider's Whisperer (TTWDFL)
        "16006", // Wheels Within Wheels (TTWDFL)
        "16007", // Prince's Loyalist (TTWDFL)
        "16008", // You Murdered Her Children (TTWDFL)
        "16009", // Samwell Tarly (TTWDFL)
        "16010", // Old Bear Mormont (TTWDFL)
        "16011", // Catelyn Stark (TTWDFL)
        "16012", // Snow Castle (TTWDFL)
        "16013", // Mad King Aerys (TTWDFL)
        "16014", // The Hatchlings' Feast (TTWDFL)
        "16015", // The Queen of Thorns (TTWDFL)
        "16016", // Olenna's Study (TTWDFL)
        "16017", // Littlefinger (TTWDFL)
        "16018", // Vale Refugee (TTWDFL)
        "16019", // High Ground (TTWDFL)
        "16020", // King's Landing (TTWDFL)
        "16021", // Harrenhal (TTWDFL)
        "16022", // Sky Cell (TTWDFL)
        "16023", // Heads on Pikes (TTWDFL)
        "16024", // Narrow Escape (TTWDFL)
        "16025", // Seductive Promise (TTWDFL)
        "16026", // Westeros Bleeds (TTWDFL)
        "16027", // Aloof and Apart (TTWDFL)
        "16028", // Dark Wings, Dark Words (TTWDFL)
        "16029", // Knights of the Realm (TTWDFL)
        "16030", // The Long Voyage (TTWDFL)
        "16031", // Benjen's Cache (TTWDFL)
        "16032", // Rioting (TTWDFL)
        "16033", // Rule By Decree (TTWDFL)
        "16034", // Search and Detain (TTWDFL)
        "16035", // The Art of Seduction (TTWDFL)
        "16036", // The Gathering Storm (TTWDFL)
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
