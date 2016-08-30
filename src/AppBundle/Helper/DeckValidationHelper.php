<?php 

namespace AppBundle\Helper;

use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Model\SlotCollectionDecorator;

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
	
	public function findProblem($deck)
	{
		$agenda = $deck->getSlots()->getAgenda();

		$drawDeck = $deck->getSlots()->getDrawDeck();
		$plotDeck = $deck->getSlots()->getPlotDeck();
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
		if($deck->getSlots()->getAgendas()->countCards() > 1) {
			return 'too_many_agendas';
		}
		if($deck->getSlots()->getDrawDeck()->countCards() < 60) {
			return 'too_few_cards';
		}
		foreach($deck->getSlots()->getCopiesAndDeckLimit() as $cardName => $value) {
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
					$minorFactionCards = new SlotCollectionDecorator($drawDeck->getSlots()->filter(function($slot) use($minorFactionCode) {
						return $slot->getCard()->getFaction()->getCode()===$minorFactionCode;
					}));
					$totalCards = $minorFactionCards->countCards();
					if($totalCards < 12) {
						return 'agenda';
					}
					break;
				}
				case '01027': {
					$drawDeck = $deck->getSlots()->getDrawDeck();
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
					$some = $plotDeck->getSlots()->filter(function($slot) use($trait) {
						return preg_match("/$trait\\./", $slot->getCard()->getTraits());
					});
					if(count($some)) {
						return 'agenda';
					}
					break;
				}
				case '05045': {
					$trait = $this->translator->trans('decks.problems_info.traits.scheme');
					$schemes = new SlotCollectionDecorator($plotDeck->getSlots()->filter(function($slot) use($trait) {
						return preg_match("/$trait\\./", $slot->getCard()->getTraits());
					}));
					$totalSchemes = $schemes->countCards();
					if($plotDeckSize != 12 || $totalSchemes != 5) {
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