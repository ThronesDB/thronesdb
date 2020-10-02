<?php

namespace App\Classes;

/**
 * Checks if a given list of cards is legal for tournament play in the Joust and Melee formats.
 * The currently implemented RLs were issued by the Playtesting Team (v2.1), effective Sept 9th, 2020.
 * @package App\Classes
 */
class RestrictedListChecker
{
    /**
     * @var array
     */
    const JOUST_RESTRICTED_CARDS = [];

    /**
     * @var array
     */
    const MELEE_RESTRICTED_CARDS = [
        "01001", // A Clash of Kings (Core)
        "01013", // Heads on Spikes (Core)
        "01043", // Superior Claim (Core)
        "01078", // Great Kraken (Core)
        "01146", // Robb Stark (Core)
        "01162", // Khal Drogo (Core)
        "02012", // Rise of the Kraken (TtB)
        "02024", // Lady Sansa's Rose (TRtW)
        "02060", // The Lord of the Crossing (TKP)
        "03003", // Eddard Stark (WotN)
        "04003", // Riverrun (AtSK)
        "04118", // Relentless Assault (TC)
        "05001", // Cersei Lannister (LoCR)
        "07036", // Plaza of Pride (WotW)
        "08013", // Nagga's Ribs (TAK)
        "08014", // Daario Naharis (TAK)
        "08098", // "The Song of the Seven" (TFM)
        "08120", // You Win Or You Die (SAT)
        "09028", // Corpse Lake (HoT)
        "11039", // Trading With Qohor (TMoW)
        "11054", // Queensguard (SoKL)
        "13107", // Robert Baratheon (LMHR)
        "17114", // Doran's Game (R)
    ];

    /**
     * @var array
     */
    const JOUST_PODS = [];

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
        return $this->isLegal($cardCodes, self::JOUST_RESTRICTED_CARDS)
            && $this->isPodsLegal($cardCodes, self::JOUST_PODS);
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

    /**
     * @param array $cards
     * @param array $pods
     * @return bool
     */
    protected function isPodsLegal(array $cards, array $pods)
    {
        $isLegal = true;
        foreach ($pods as $pod) {
            $restricted = $pod['restricted'];
            if (! in_array($restricted, $cards)) {
                continue;
            }
            if (array_intersect($pod['cards'], $cards)) {
                $isLegal = false;
                break;
            }
        }
        return $isLegal;
    }
}
