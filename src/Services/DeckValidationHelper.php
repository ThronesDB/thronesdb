<?php

namespace App\Services;

use App\Classes\SlotCollectionInterface;
use App\Entity\CardInterface;
use App\Entity\CommonDeckInterface;
use App\Entity\SlotInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @package App\Services
 */
class DeckValidationHelper
{
    protected AgendaHelper $agenda_helper;

    protected TranslatorInterface  $translator;

    /**
     * DeckValidationHelper constructor.
     * @param AgendaHelper $agenda_helper
     * @param TranslatorInterface $translator
     */
    public function __construct(AgendaHelper $agenda_helper, TranslatorInterface $translator)
    {
        $this->agenda_helper = $agenda_helper;
        $this->translator = $translator;
    }

    /**
     * @param CommonDeckInterface $deck
     * @return string|null
     */
    public function findProblem(CommonDeckInterface $deck)
    {
        $slots = $deck->getSlots();
        $plotDeck = $slots->getPlotDeck();
        $plotDeckSize = $plotDeck->countCards();

        $expectedPlotDeckSize = 7;
        $expectedMaxDoublePlot = 1;
        $expectedMaxAgendaCount = 1;
        $expectedMinCardCount = 60;

        foreach ($slots->getAgendas() as $agenda) {
            $code = $agenda->getCard()->getCode();
            switch ($code) {
                case '05045': // The Wars To Come
                    $expectedPlotDeckSize = 12;
                    break;
                case '10045': // "The Rains of Castamere"
                    $expectedPlotDeckSize = 10;
                    $expectedMaxDoublePlot = 2;
                    break;
                case '13118': // Valyrian Steel (BtRK)
                case '17152': // Valyrian Steel (R)
                case '16028': // Dark Wings, Dark Words
                    $expectedMinCardCount = 75;
                    break;
                case '16030': // The Long Voyage
                    $expectedMinCardCount = 100;
                    break;
                default:
                    // do nothing here
            }
        }
        if ($plotDeckSize > $expectedPlotDeckSize) {
            return 'too_many_plots';
        }
        if ($plotDeckSize < $expectedPlotDeckSize) {
            return 'too_few_plots';
        }
        // Expected number of different plots
        $expectedPlotDeckSpread = $expectedPlotDeckSize - $expectedMaxDoublePlot;
        if (count($plotDeck) < $expectedPlotDeckSpread) {
            return 'too_many_different_plots';
        }

        if ($slots->isAlliance()) {
            $expectedMaxAgendaCount = 3;
            $expectedMinCardCount = 75;
        }

        if ($slots->getAgendas()->countCards() > $expectedMaxAgendaCount) {
            return 'too_many_agendas';
        }
        if ($slots->getDrawDeck()->countCards() < $expectedMinCardCount) {
            return 'too_few_cards';
        }
        foreach ($slots->getCopiesAndDeckLimit() as $cardName => $value) {
            if ($value['copies'] > $value['deck_limit']) {
                return 'too_many_copies';
            }
        }
        if (!empty($this->getInvalidCards($deck))) {
            return 'invalid_cards';
        }
        foreach ($slots->getAgendas() as $slot) {
            $valid_agenda = $this->validateAgenda($slots, $slot->getCard());
            if (!$valid_agenda) {
                return 'agenda';
            }
        }

        return null;
    }

    /**
     * @param string|null $problem
     * @return string
     */
    public function getProblemLabel($problem): string
    {
        if (!$problem) {
            return '';
        }

        return $this->translator->trans('decks.problems.'.$problem);
    }

    /**
     * @param CommonDeckInterface $deck
     * @param CardInterface $card
     * @return bool
     */
    public function canIncludeCard(CommonDeckInterface $deck, CardInterface $card): bool
    {
        if ($card->getFaction()->getCode() === 'neutral') {
            return true;
        }
        if ($card->getFaction()->getCode() === $deck->getFaction()->getCode()) {
            return true;
        }
        if ($card->getIsLoyal()) {
            return false;
        }
        foreach ($deck->getSlots()->getAgendas() as $slot) {
            if ($this->isCardAllowedByAgenda($slot->getCard(), $card)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param CommonDeckInterface $deck
     * @return array
     */
    protected function getInvalidCards(CommonDeckInterface $deck): array
    {
        $invalidCards = [];
        foreach ($deck->getSlots() as $slot) {
            if (!$this->canIncludeCard($deck, $slot->getCard())) {
                $invalidCards[] = $slot->getCard();
            }
        }

        return $invalidCards;
    }

    /**
     * @param CardInterface $agenda
     * @param CardInterface $card
     * @return bool
     */
    protected function isCardAllowedByAgenda(CardInterface $agenda, CardInterface $card): bool
    {
        switch ($agenda->getCode()) {
            case '01198':
            case '01199':
            case '01200':
            case '01201':
            case '01202':
            case '01203':
            case '01204':
            case '01205':
                return $this->agenda_helper->getMinorFactionCode($agenda) === $card->getFaction()->getCode();
            case '09045': // The Conclave
                $trait = $this->translator->trans('card.traits.maester');
                if (preg_match("/$trait\\./", $card->getTraits())) {
                    return $card->getType()->getCode() === 'character';
                }
                return false;
            case '13079': // Kingdom of Shadows (BtRK)
            case '17148': // Kingdom of Shadows (R)
                $langKey = $this->translator->trans('card.keywords.shadow');
                return $card->getType()->getCode() === 'character' && $card->hasShadowKeyword($langKey);
            case '13099': // The White Book
                $trait = $this->translator->trans('card.traits.kingsguard');
                if (preg_match("/$trait\\./", $card->getTraits())) {
                    return $card->getType()->getCode() === 'character';
                }
                return false;
        }
        return false;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @param CardInterface $agenda
     * @return bool
     */
    protected function validateAgenda(SlotCollectionInterface $slots, CardInterface $agenda): bool
    {
        switch ($agenda->getCode()) {
            case '01198':
            case '01199':
            case '01200':
            case '01201':
            case '01202':
            case '01203':
            case '01204':
            case '01205':
                return $this->validateBanner($slots, $agenda);
            case '01027':
                return $this->validateFealty($slots);
            case '04037':
            case '04038':
                return $this->validateKings($slots, $agenda);
            case '05045':
                return $this->validateRains($slots);
            case '06018':
                return $this->validateAlliance($slots);
            case '06119':
                return $this->validateBrotherhood($slots);
            case '09045':
                return $this->validateConclave($slots);
            case '11079':
                return $this->validateFreeFolk($slots);
            case '13099':
                return $this->validateTheWhiteBook($slots);
            case '13118':
                return $this->validateValyrianSteel($slots);
            case '17152':
                return $this->validateRedesignedValyrianSteel($slots);
            case '16028':
                return $this->validateDarkWingsDarkWords($slots);
            case '17149':
                return $this->validateRedesignedSeaOfBlood($slots);
            default:
                return true;
        }
    }

    protected function validateRedesignedSeaOfBlood($slots): bool
    {
        $eventSlots = $slots->getDrawDeck()->filterByType('event');
        foreach ($eventSlots as $slot) {
            if ($slot->getCard()->getFaction()->getCode() === 'neutral') {
                return false;
            }
        }
        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @param CardInterface $agenda
     * @return bool
     */
    protected function validateBanner(SlotCollectionInterface $slots, CardInterface $agenda): bool
    {
        $minorFactionCode = $this->agenda_helper->getMinorFactionCode($agenda);
        $matchingFactionPlots = $slots->getDrawDeck()->filterByFaction($minorFactionCode)->countCards();
        if ($matchingFactionPlots < 12) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateFealty(SlotCollectionInterface $slots): bool
    {
        $drawDeck = $slots->getDrawDeck();
        $count = 0;
        foreach ($drawDeck as $slot) {
            if ($slot->getCard()->getFaction()->getCode() === 'neutral') {
                $count += $slot->getQuantity();
            }
        }
        if ($count > 15) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @param CardInterface $agenda
     * @return bool
     */
    protected function validateKings(SlotCollectionInterface $slots, CardInterface $agenda): bool
    {
        $trait = $this->translator->trans('card.traits.'.($agenda->getCode() === '04037' ? 'winter' : 'summer'));
        $matchingTraitPlots = $slots->getPlotDeck()->filterByTrait($trait)->countCards();
        if ($matchingTraitPlots > 0) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateRains(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.scheme');
        $matchingTraitPlots = $slots->getPlotDeck()->filterByTrait($trait);
        $matchingTraitPlotsUniqueCount = $matchingTraitPlots->count();
        $matchingTraitPlotsTotalCount = $matchingTraitPlots->countCards();
        if ($matchingTraitPlotsUniqueCount !== 5 || $matchingTraitPlotsTotalCount !== 5) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateAlliance(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.banner');
        $agendas = $slots->getAgendas();
        $matchingTraitAgendas = $agendas->filterByTrait($trait);
        if ($agendas->countCards() - $matchingTraitAgendas->countCards() !== 1) {
            return false;
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateBrotherhood(SlotCollectionInterface $slots): bool
    {
        /* @var SlotInterface $slot */
        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getIsLoyal() && $card->getType()->getCode() === 'character') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateConclave(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.maester');
        $matchingMaesters = $slots->getDrawDeck()->filterByTrait($trait)->countCards();
        if ($matchingMaesters < 12) {
            return false;
        }

        return true;
    }

    /**
     * Special deck validation rules for the "The White Book" agenda.
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateTheWhiteBook(SlotCollectionInterface $slots): bool
    {
        $trait = $this->translator->trans('card.traits.kingsguard');
        $slots = $slots->getDrawDeck()->filterByTrait($trait);
        $names = [];
        foreach ($slots as $slot) {
            $names[] = $slot->getCard()->getName();
        }
        return count(array_unique($names)) >= 7;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateFreeFolk(SlotCollectionInterface $slots): bool
    {
        foreach ($slots->getPlotDeck()->getSlots() as $slot) {
            /* @var CardInterface $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            /* @var CardInterface $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateValyrianSteel(SlotCollectionInterface $slots): bool
    {
        // Your deck cannot include more than 1 copy of each attachment (by title).
        $names = [];

        $nonAttachmentSlots = $slots->getDrawDeck()->excludeByType('attachment');
        foreach ($nonAttachmentSlots as $slot) {
            $names[] = $slot->getCard()->getName();
        }

        $attachmentsSlots = $slots->getDrawDeck()->filterByType('attachment');
        /* @var SlotInterface $slot */
        foreach ($attachmentsSlots as $slot) {
            $name = $slot->getCard()->getName();
            if (in_array($name, $names)) {
                return false;
            }
            if (1 < $slot->getQuantity()) {
                return false;
            }
            $names[] = $name;
        }
        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateRedesignedValyrianSteel(SlotCollectionInterface $slots): bool
    {
        // Your deck cannot include more than 1 copy of each attachment.
        $names = [];

        $attachmentsSlots = $slots->getDrawDeck()->filterByType('attachment');
        /* @var SlotInterface $slot */
        foreach ($attachmentsSlots as $slot) {
            $name = $slot->getCard()->getName();
            if (in_array($name, $names)) {
                return false;
            }
            if (1 < $slot->getQuantity()) {
                return false;
            }
            $names[] = $name;
        }
        return true;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateDarkWingsDarkWords(SlotCollectionInterface $slots): bool
    {
        // Your deck cannot include more than 1 copy of each event (by title).
        $names = [];

        $nonAttachmentSlots = $slots->getDrawDeck()->excludeByType('event');
        foreach ($nonAttachmentSlots as $slot) {
            $names[] = $slot->getCard()->getName();
        }

        $attachmentsSlots = $slots->getDrawDeck()->filterByType('event');
        /* @var SlotInterface $slot */
        foreach ($attachmentsSlots as $slot) {
            $name = $slot->getCard()->getName();
            if (in_array($name, $names)) {
                return false;
            }
            if (1 < $slot->getQuantity()) {
                return false;
            }
            $names[] = $name;
        }
        return true;
    }
}
