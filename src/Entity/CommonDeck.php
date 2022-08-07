<?php

namespace App\Entity;

use stdClass;

/**
 * Base class for both deck and deck-list entities.
 * @package App\Entity
 */
abstract class CommonDeck implements CommonDeckInterface
{
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
}
