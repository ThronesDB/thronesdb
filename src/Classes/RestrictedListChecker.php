<?php

namespace App\Classes;

use App\Entity\Decklistslot;
use Doctrine\Common\Collections\Collection;

/**
 * Checks if a given list of cards is legal for tournament play in the Joust and Melee formats.
 * The currently implemented RLs were issued by The Conclave (v2.0), effective July 3rd, 2020.
 * @package App\Classes
 */
class RestrictedListChecker
{
    /**
     * @var array
     */
    const JOUST_RESTRICTED_CARDS = [
        "02034", // Crown of Gold (TRtW)
        "02065", // Halder (NMG)
        "02091", // Raider from Pyke (CoW)
        "02092", // Iron Mines (CoW)
        "02102", // Ward (TS)
        "06004", // All Men Are Fools (AMAF)
        "06038", // Great Hall (GtR)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06098", // Flea Bottom (OR)
        "08080", // The King in the North (FotOG)
        "08082", // I Am No One (TFM)
        "09001", // Mace Tyrell (HoT)
        "09037", // Qotho (HoT)
        "09051", // Trade Routes (HoT)
        "10045", // The Wars To Come (SoD)
        "10048", // Forced March (SoD)
        "10050", // Breaking Ties (SoD)
        "11012", // Nighttime Marauders (TSC)
        "11021", // Wyman Manderly (TMoW)
        "11033", // Hizdahr zo Loraq (TMoW)
        "11034", // Meereen (TMoW)
        "11051", // Drowned God Fanatic (SoKL)
        "11061", // Meera Reed (MoD)
        "11114", // Gifts for the Widow (DitD)
        "12002", // Euron Crow's Eye (KotI)
        "12029", // Desert Raider (KotI)
        "12046", // We Take Westeros! (KotI)
        "12047", // Return to the Fields (KotI)
        "13044", // Unexpected Guile (PoS)
        "13085", // Yoren (TB)
        "13086", // Bound for the Wall (TB)
        "13103", // The Queen's Retinue (LMHR)
        "14008", // Selyse Baratheon (FotS)
        "15030", // The Red Keep (DotE)
        "15033", // Clydas (DotE)
        "15045", // Bribery (DotE)
        "16027", // Aloof and Apart (TTWDFL)
    ];

    /**
     * @var array
     */
    const MELEE_RESTRICTED_CARDS = [
        "01001", // A Clash of Kings (Core)
        "01013", // Heads on Spikes (Core)
        "01043", // Superior Claim (Core)
        "01078", // Great Kraken (Core)
        "01119", // Doran's Game (Core)
        "01146", // Robb Stark (Core)
        "01162", // Khal Drogo (Core)
        "02012", // Rise of the Kraken (TtB)
        "02024", // Lady Sansa's Rose (TRtW)
        "02060", // The Lord of the Crossing (TKP)
        "03003", // Eddard Stark (WotN)
        "04003", // Riverrun (AtSK)
        "04118", // Relentless Assault (TC)
        "05001", // Cersei Lannister (LoCR)
        "06004", // All Men Are Fools (AMAF)
        "06011", // Drowned Disciple (AMAF)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06098", // Flea Bottom (OR)
        "07036", // Plaza of Pride (WotW)
        "08013", // Nagga's Ribs (TAK)
        "08014", // Daario Naharis (TAK)
        "08082", // I Am No One (TFM)
        "08098", // "The Song of the Seven" (TFM)
        "08120", // You Win Or You Die (SAT)
        "09001", // Mace Tyrell (HoT)
        "09028", // Corpse Lake (HoT)
        "11039", // Trading With Qohor (TMoW)
        "11054", // Queensguard (SoKL)
        "13107", // Robert Baratheon (LMHR)
        "15045", // Bribery (DotE)
    ];

    /**
     * @var array
     */
    const JOUST_PODS = [
        [
            "name" => "P1",
            "restricted" => "13085", // Yoren (TB)
            "cards" => [
                "04026", // Craven (CtA)
                "11085", // Three-Finger Hobb (IDP)
            ],
        ],
        [
            "name" => "P2",
            "restricted" => "11051", // Drowned God Fanatic (SoKL)
            "cards" => [
                "06011", // Drowned Disciple (AMAF)
            ],
        ],
        [
            "name" => "P3",
            "restricted" => "11114", // Gifts for the Widow (DitD)
            "cards" => [
                "15001", // Daenerys Targaryen (DotE)
            ]
        ],
        [
            "name" => "P4",
            "restricted" => "09037", // Qotho (HoT)
            "cards" => [
                "15017", // Womb of the World (DotE)
            ]
        ],
        [
            "name" => "P5",
            "restricted" => "09001", // Mace Tyrell (HoT)
            "cards" => [
                "09017", // The Hightower (HoT)
            ]
        ],
        [
            "name" => "P6",
            "restricted" => "12029", // Desert Raider (KotI)
            "cards" => [
                "06011", // Drowned Disciple (AMAF)
            ]
        ],
        [
            "name" => "P7",
            "restricted" => "11021", // Wyman Manderly (TMoW)
            "cards" => [
                "11081", // Bear Island Scout (IDP)
                "11082", // Skagos (IDP)
            ]
        ],
        [
            "name" => "P8",
            "restricted" => "06040", // The Annals of Castle Black (GtR)
            "cards" => [
                "06063", // Oldtown Informer (TRW)
                "06100", // Wheels Within Wheels (OR)

            ]
        ],
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
