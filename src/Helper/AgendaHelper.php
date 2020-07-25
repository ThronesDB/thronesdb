<?php

namespace App\Helper;

use App\Entity\CardInterface;
use App\Entity\Faction;
use App\Entity\FactionInterface;
use Doctrine\ORM\EntityManager;

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
     * @param CardInterface $agenda
     * @return string
     */
    public function getMinorFactionCode(CardInterface $agenda)
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
     * @param CardInterface $agenda
     * @return FactionInterface
     */
    public function getMinorFaction(CardInterface $agenda)
    {
        $code = $this->getMinorFactionCode($agenda);
        if ($code) {
            return $this->entityManager->getRepository(Faction::class)->findOneBy([ 'code' => $code ]);
        }
        return null;
    }
}
