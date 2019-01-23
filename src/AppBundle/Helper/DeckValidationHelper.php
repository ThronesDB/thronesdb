<?php

namespace AppBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Model\SlotCollectionProviderInterface;

class DeckValidationHelper
{
    public function __construct(AgendaHelper $agenda_helper, TranslatorInterface $translator)
    {
        $this->agenda_helper = $agenda_helper;
        $this->translator = $translator;
    }

    public function getInvalidCards($deck)
    {
        $invalidCards = [];
        foreach ($deck->getSlots() as $slot) {
            if (!$this->canIncludeCard($deck, $slot->getCard())) {
                $invalidCards[] = $slot->getCard();
            }
        }
        return $invalidCards;
    }

    public function canIncludeCard($deck, $card)
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

    public function isCardAllowedByAgenda($agenda, $card)
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
            case '09045':
                $trait = $this->translator->trans('card.traits.maester');
                if (preg_match("/$trait\\./", $card->getTraits())) {
                    return $card->getType()->getCode() === 'character';
                }
                return false;
        }

        return false;
    }

    public function findProblem(SlotCollectionProviderInterface $deck)
    {
        $slots = $deck->getSlots();

        $plotDeck = $slots->getPlotDeck();
        $plotDeckSize = $plotDeck->countCards();

        /* @var integer $expectedPlotDeckSize Expected number of plots */
        $expectedPlotDeckSize = 7;
        $expectedMaxDoublePlot = 1;
        foreach ($slots->getAgendas() as $agenda) {
            if ($agenda->getCard()->getCode() === '05045') {
                $expectedPlotDeckSize = 12;
            } elseif ($agenda->getCard()->getCode() === '10045') {
                $expectedPlotDeckSize = 10;
                $expectedMaxDoublePlot = 2;
            }
        }
        if ($plotDeckSize > $expectedPlotDeckSize) {
            return 'too_many_plots';
        }
        if ($plotDeckSize < $expectedPlotDeckSize) {
            return 'too_few_plots';
        }
        /* @var integer $expectedPlotDeckSpread Expected number of different plots */
        $expectedPlotDeckSpread = $expectedPlotDeckSize - $expectedMaxDoublePlot;
        if (count($plotDeck) < $expectedPlotDeckSpread) {
            return 'too_many_different_plots';
        }
        $expectedMaxAgendaCount = 1;
        $expectedMinCardCount = 60;
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

    public function validateAgenda(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
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
                return $this->validateFealty($slots, $agenda);
            case '04037':
            case '04038':
                return $this->validateKings($slots, $agenda);
            case '05045':
                return $this->validateRains($slots, $agenda);
            case '06018':
                return $this->validateAlliance($slots, $agenda);
            case '06119':
                return $this->validateBrotherhood($slots, $agenda);
            case '09045':
                return $this->validateConclave($slots, $agenda);
            case '11079':
                return $this->validateFreeFolk($slots, $agenda);
            default:
                return true;
        }
    }

    public function validateBanner(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        $minorFactionCode = $this->agenda_helper->getMinorFactionCode($agenda);
        $matchingFactionPlots = $slots->getDrawDeck()->filterByFaction($minorFactionCode)->countCards();
        if ($matchingFactionPlots < 12) {
            return false;
        }
        return true;
    }

    public function validateFealty(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
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

    public function validateKings(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        $trait = $this->translator->trans('card.traits.' . ($agenda->getCode() === '04037' ? 'winter' : 'summer'));
        $matchingTraitPlots = $slots->getPlotDeck()->filterByTrait($trait)->countCards();
        if ($matchingTraitPlots > 0) {
            return false;
        }
        return true;
    }

    public function validateRains(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
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
    
    public function validateAlliance(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        $trait = $this->translator->trans('card.traits.banner');
        $agendas = $slots->getAgendas();
        $matchingTraitAgendas = $agendas->filterByTrait($trait);
        if ($agendas->countCards() - $matchingTraitAgendas->countCards() !== 1) {
            return false;
        }
        return true;
    }

    public function validateBrotherhood(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getIsLoyal() && $card->getType()->getCode() === 'character') {
                return false;
            }
        }
        return true;
    }

    public function validateConclave(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        $trait = $this->translator->trans('card.traits.maester');
        $matchingMaesters = $slots->getDrawDeck()->filterByTrait($trait)->countCards();
        if ($matchingMaesters < 12) {
            return false;
        }
        return true;
    }

    public function validateFreeFolk(\AppBundle\Model\SlotCollectionInterface $slots, \AppBundle\Entity\Card $agenda)
    {
        foreach ($slots->getPlotDeck()->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        return true;
    }

    public function getProblemLabel($problem)
    {
        if (!$problem) {
            return '';
        }
        return $this->translator->trans('decks.problems.' . $problem);
    }
}
