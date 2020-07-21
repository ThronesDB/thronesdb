<?php

namespace App\Entity;

/**
 * Base class for both deck and deck-list entities.

 * @package App\Entity
 */
abstract class BaseDeck implements CommonDeckInterface
{
    /**
     * @inheritdoc
     */
    public function getArrayExport()
    {
        $slots = $this->getSlots();
        $agendas = $slots->getAgendas();
        $agendas_code = [];
        $agenda_urls = [];
        foreach ($agendas as $agenda) {
            $agendas_code[] = $agenda->getCard()->getCode();
            $agendas_urls[] = $agenda->getCard()->getImageUrl();
        }
        $array = [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'date_creation' => $this->getDateCreation()->format('c'),
            'date_update' => $this->getDateUpdate()->format('c'),
            'description_md' => $this->getDescriptionMd(),
            'user_id' => $this->getUser()->getId(),
            'faction_code' => $this->getFaction()->getCode(),
            'faction_name' => $this->getFaction()->getName(),
            'slots' => $slots->getContent(),
            'agendas' => $agendas_code,
            'agendaurls' => $agenda_urls,
            'version' => $this->getVersion(),
            'isLegalForJoust' => $this->isLegalForJoust(),
            'isLegalForMelee' => $this->isLegalForMelee(),
        ];

        return $array;
    }

    /**
     * @inheritdoc
     */
    public function getTextExport()
    {
        $slots = $this->getSlots();
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'agendas' => $slots->getAgendas(),
            'faction' => $this->getFaction(),
            'draw_deck_size' => $slots->getDrawDeck()->countCards(),
            'plot_deck_size' => $slots->getPlotDeck()->countCards(),
            'included_packs' => $slots->getIncludedPacks(),
            'slots_by_type' => $slots->getSlotsByType()
        ];
    }

    /**
     * @inheritdoc
     */
    public function getCycleOrderExport()
    {
        $slots = $this->getSlots();
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'agendas' => $slots->getAgendas(),
            'faction' => $this->getFaction(),
            'draw_deck_size' => $slots->getDrawDeck()->countCards(),
            'plot_deck_size' => $slots->getPlotDeck()->countCards(),
            'included_packs' => $slots->getIncludedPacks(),
            'slots_by_cycle_order' => $slots->getSlotsByCycleOrder()
        ];
    }

    /**
     * @inheritdoc
     */
    public function isLegalForMelee()
    {
        return $this->getSlots()->isLegalForMelee();
    }

    /**
     * @inheritdoc
     */
    public function isLegalForJoust()
    {
        return $this->getSlots()->isLegalForJoust();
    }
}
