<?php

namespace App\Classes;

use App\Entity\Decklistslot;
use Doctrine\Common\Collections\Collection;

/**
 * Checks if a given list of cards is legal for tournament play in the Joust and Melee formats.
 * The currently implemented RLs were issued by The Conclave (v1.0), effective April 13th, 2020.
 * @package App\Classes
 */
class RestrictedListChecker
{
    /**
     * @var array
     */
    const JOUST_RESTRICTED_CARDS = [
        "01109", // The Red Viper (Core)
        "02091", // Raider from Pyke (CoW)
        "02092", // Iron Mines (CoW)
        "02102", // Ward (TS)
        "03038", // To the Rose Banner! (WotN)
        "04001", // The Dragon's Tail (AtSK)
        "04017", // Tower of the Sun (AtSK)
        "05010", // Taena Merryweather (LoCR)
        "05049", // Littlefinger's Meddling (LoCR)
        "06004", // All Men Are Fools (AMAF)
        "06011", // Drowned Disciple (AMAF)
        "06038", // Great Hall (GtR)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06063", // Oldtown Informer (TRW)
        "06098", // Flea Bottom (OR)
        "06100", // Wheels Within Wheels (OR)
        "06103", // Highgarden Minstrel (TBWB)
        "08080", // The King in the North (FotOG)"
        "09001", // Mace Tyrell (HoT)
        "09017", // The Hightower (HoT)
        "09023", // "Six Maids in a Pool" (HoT)
        "09037", // Qotho (HoT)"
        "09051", // Trade Routes (HoT)
        "10045", // The Wars To Come (SoD)
        "10048", // Forced March (SoD)
        "10050", // Breaking Ties (SoD)
        "11012", // Nighttime Marauders (TSC)"
        "11021", // Wyman Manderly (TMoW)
        "11033", // Hizdahr zo Loraq (TMoW)
        "11034", // Meereen (TMoW)
        "11044", // Growing Ambition (SoKL)
        "11051", // Drowned God Fanatic (SoKL)
        "11061", // Meera Reed (MoD)
        "11076", // A Mission in Essos (MoD)
        "11082", // Skagos (IDP)
        "11085", // Three-Finger Hobb (IDP)"
        "11114", // Gifts for the Widow (DitD)"
        "12002", // Euron Crow's Eye (KotI)
        "12029", // Desert Raider (KotI)
        "12045", // Sea of Blood (KotI)
        "12046", // We Take Westeros! (KotI)
        "12047", // Return to the Fields (KotI)"
        "13044", // Unexpected Guile (PoS)
        "13079", // Kingdom of Shadows (BtRK)"
        "13085", // Yoren (TB)
        "13086", // Bound for the Wall (TB)
        "13103", // The Queen's Retinue (LMHR)
        "14008", // Selyse Baratheon (FotS)"
        "15022", // Overwhelming Numbers (DotE)"
        "15030", // The Red Keep (DotE)"
        "15033", // Clydas (DotE)"
        "15045", // Bribery (DotE)"
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
        "01162", // Khal Drogo (Core)
        "02012", // Rise of the Kraken (TtB)
        "02024", // Lady Sansa's Rose (TRtW)
        "02060", // The Lord of the Crossing (TKP)
        "03003", // Eddard Stark (WotN)
        "03038", // To the Rose Banner! (WotN)
        "04003", // Riverrun (AtSK)
        "04118", // Relentless Assault (TC)
        "05001", // Cersei Lannister (LoCR)
        "05010", // Taena Merryweather (LoCR)
        "05049", // Littlefinger's Meddling (LoCR)
        "06004", // All Men Are Fools (AMAF)
        "06011", // Drowned Disciple (AMAF)
        "06039", // "The Dornishman's Wife" (GtR)
        "06040", // The Annals of Castle Black (GtR)
        "06098", // Flea Bottom (OR)
        "07036", // Plaza of Pride (WotW)
        "08098", // "The Song of the Seven" (TFM)
        "08120", // You Win Or You Die (SAT)
        "09028", // Corpse Lake (HoT)
        "11054", // Queensguard (SoKL)
        "11076", // A Mission in Essos (MoD)
        "13107", // Robert Baratheon (LMHR)
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
