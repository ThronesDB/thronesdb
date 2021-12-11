<?php

namespace App\Services;

use App\Entity\CardInterface;
use App\Entity\Faction;
use App\Entity\FactionInterface;
use Doctrine\ORM\EntityManagerInterface;

class AgendaHelper
{
    protected EntityManagerInterface $doctrine;

    public function __construct(EntityManagerInterface $doctrine)
    {
        $this->doctrine = $doctrine;
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
            return $this->doctrine->getRepository(Faction::class)->findOneBy([ 'code' => $code ]);
        }
        return null;
    }

    /**
     * @param CardInterface $agenda
     * @return string
     * @todo added here for convenience, this should probably be part of a generic string helper class. [ST 2021/12/11]
     */
    public function agendaToTag(CardInterface $agenda): string
    {
        $name = $agenda->getName();
        return preg_replace('/[^a-zA-Z]/', '', strtolower($name));
    }
}
