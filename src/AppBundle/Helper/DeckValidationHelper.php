<?php 

namespace AppBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Model\SlotCollectionDecorator;
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
		foreach ( $deck->getSlots() as $slot ) {
			if(! $this->canIncludeCard($deck, $slot->getCard())) {
				$invalidCards[] = $slot->getCard();
			}
		}
		return $invalidCards;
	}
	
	public function canIncludeCard($deck, $card) {
		if($card->getFaction()->getCode() === 'neutral') {
			return true;
		}
		if($card->getFaction()->getCode() === $deck->getFaction()->getCode()) {
			return true;
		}
		if($card->getIsLoyal()) {
			return false;
		}
		$agenda = $deck->getSlots()->getAgenda();
		if($agenda && $this->agenda_helper->getMinorFactionCode($agenda) === $card->getFaction()->getCode()) {
			return true;
		}
		return false;
	}
	
	public function findProblem(SlotCollectionProviderInterface $deck)
	{
		$slots = $deck->getSlots();
		
		$agenda = $slots->getAgenda();

		$plotDeck = $slots->getPlotDeck();
		$plotDeckSize = $plotDeck->countCards();

		//check plot size only if no agenda is used or this agenda is not Rains of Castamere
		if(!$agenda || $agenda->getCode()!='05045')
		{
			if($plotDeckSize > 7) {
				return 'too_many_plots';
			}
			if($plotDeckSize < 7) {
				return 'too_few_plots';
			}
		}
		if(count($plotDeck) < 6) {
			return 'too_many_different_plots';
		}
		if($slots->getAgendas()->countCards() > 1) {
			return 'too_many_agendas';
		}
		if($slots->getDrawDeck()->countCards() < 60) {
			return 'too_few_cards';
		}
		foreach($slots->getCopiesAndDeckLimit() as $cardName => $value) {
			if($value['copies'] > $value['deck_limit']) return 'too_many_copies';
		}
		if(!empty($this->getInvalidCards($deck))) {
			return 'invalid_cards';
		}
		if($agenda) {	
			switch($agenda->getCode()) {
				case '01198':
				case '01199':
				case '01200':
				case '01201':
				case '01202':
				case '01203':
				case '01204':
				case '01205': {
					$minorFactionCode = $this->agenda_helper->getMinorFactionCode($agenda);
					$totalCards = $slots->getDrawDeck()->filterByFaction($minorFactionCode)->countCards();
					if($totalCards < 12) {
						return 'agenda';
					}
					break;
				}
				case '01027': {
					$drawDeck = $slots->getDrawDeck();
					$count = 0;
					foreach($drawDeck as $slot) {
						if($slot->getCard()->getFaction()->getCode() === 'neutral') {
							$count += $slot->getQuantity();
						}
					}
					if($count > 15) {
						return 'agenda';
					}
					break;
				}
				case '04037':
				case '04038': {					
					$trait = $this->translator->trans('decks.problems_info.traits.'.($agenda->getCode()=='04037' ? 'winter' : 'summer'));
					$totalPlots = $plotDeck->filterByTrait($trait)->countCards();
					if($totalPlots > 0) {
						return 'agenda';
					}
					break;
				}
				case '05045': {
					$trait = $this->translator->trans('decks.problems_info.traits.scheme');
					$totalPlots = $plotDeck->filterByTrait($trait)->countCards();
					if($plotDeckSize != 12 || $totalPlots != 5) {
						return 'agenda';
					}
					break;
				}
			}
	
		}
		return null;
	}
	
	public function getProblemLabel($problem) {
		if(! $problem) {
			return '';
		}
		return $this->translator->trans('decks.problems.'.$problem);
	}
	
	
}
