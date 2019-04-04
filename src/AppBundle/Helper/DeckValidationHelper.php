<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Card;
use AppBundle\Model\ExportableDeck;
use AppBundle\Model\SlotCollectionInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class DeckValidationHelper
 * @package AppBundle\Helper
 */
class DeckValidationHelper
{
    /**
     * @var AgendaHelper
     */
    protected $agenda_helper;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

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
     * @param ExportableDeck $deck
     * @return string|null
     */
    public function findProblem(ExportableDeck $deck)
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
     * @param ExportableDeck $deck
     * @param Card $card
     * @return bool
     */
    public function canIncludeCard(ExportableDeck $deck, Card $card): bool
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
     * @param ExportableDeck $deck
     * @return array
     */
    protected function getInvalidCards(ExportableDeck $deck): array
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
     * @param Card $agenda
     * @param Card $card
     * @return bool
     */
    protected function isCardAllowedByAgenda(Card $agenda, Card $card): bool
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
            case '13079': // Kingdom of Shadows
                $langKey = $this->translator->trans('card.keywords.shadow');
                return $card->getType()->getCode() === 'character' && $card->hasShadowKeyword($langKey);

        }
        return false;
    }

    /**
     * @param SlotCollectionInterface $slots
     * @param Card $agenda
     * @return bool
     */
    protected function validateAgenda(SlotCollectionInterface $slots, Card $agenda): bool
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
            default:
                return true;
        }
    }

    /**
     * @param SlotCollectionInterface $slots
     * @param Card $agenda
     * @return bool
     */
    protected function validateBanner(SlotCollectionInterface $slots, Card $agenda): bool
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
     * @param Card $agenda
     * @return bool
     */
    protected function validateKings(SlotCollectionInterface $slots, Card $agenda): bool
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
        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            /* @var Card $card */
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
     * @param SlotCollectionInterface $slots
     * @return bool
     */
    protected function validateFreeFolk(SlotCollectionInterface $slots): bool
    {
        foreach ($slots->getPlotDeck()->getSlots() as $slot) {
            /* @var Card $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        foreach ($slots->getDrawDeck()->getSlots() as $slot) {
            /* @var Card $card */
            $card = $slot->getCard();
            if ($card->getFaction()->getCode() !== 'neutral') {
                return false;
            }
        }

        return true;
    }
}
