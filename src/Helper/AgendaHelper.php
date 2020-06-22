<?php

namespace App\Helper;

use Doctrine\ORM\EntityManager;
use App\Entity\Card;
use App\Entity\Faction;

class AgendaHelper
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Get the minor faction code
     * @param Card $agenda
     * @return string
     */
    public function getMinorFactionCode(Card $agenda)
    {
        if (empty($agenda)) {
            return null;
        }

        // special case for the Core Set Banners
        $banners_core_set = [
                '01198' => 'baratheon',
                '01199' => 'greyjoy',
                '01200' => 'lannister',
                '01201' => 'martell',
                '01202' => 'thenightswatch',
                '01203' => 'stark',
                '01204' => 'targaryen',
                '01205' => 'tyrell'
        ];
        if (isset($banners_core_set[$agenda->getCode()])) {
            return $banners_core_set[$agenda->getCode()];
        }
        return null;
    }

    /**
     * Get the minor faction
     * @param Card $agenda
     * @return Faction
     */
    public function getMinorFaction(Card $agenda)
    {
        $code = $this->getMinorFactionCode($agenda);
        if ($code) {
            return $this->entityManager->getRepository(Faction::class)->findOneBy([ 'code' => $code ]);
        }
        return null;
    }
}
