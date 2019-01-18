<?php

namespace AppBundle\Model;

use AppBundle\Classes\RestrictedListChecker;
use AppBundle\Entity\Decklistslot;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Decorator for a collection of SlotInterface
 */
class SlotCollectionDecorator implements \AppBundle\Model\SlotCollectionInterface
{
    protected $slots;

    /**
     * @var RestrictedListChecker
     */
    protected $restrictedListChecker;

    public function __construct(\Doctrine\Common\Collections\Collection $slots)
    {
        $this->slots = $slots;
        $this->restrictedListChecker = new RestrictedListChecker();
    }

    public function add($element)
    {
        return $this->slots->add($element);
    }

    public function removeElement($element)
    {
        return $this->slots->removeElement($element);
    }

    public function count($mode = null)
    {
        return $this->slots->count($mode);
    }

    public function getIterator()
    {
        return $this->slots->getIterator();
    }

    public function offsetExists($offset)
    {
        return $this->slots->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->slots->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->slots->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->slots->offsetUnset($offset);
    }

    public function countCards()
    {
        $count = 0;
        foreach ($this->slots as $slot) {
            $count += $slot->getQuantity();
        }
        return $count;
    }

    public function getIncludedPacks()
    {
        $packs = [];
        foreach ($this->slots as $slot) {
            $card = $slot->getCard();
            $pack = $card->getPack();
            if (!isset($packs[$pack->getId()])) {
                $packs[$pack->getId()] = [
                    'pack' => $pack,
                    'nb' => 0
                ];
            }

            $nbpacks = ceil($slot->getQuantity() / $card->getQuantity());
            if ($packs[$pack->getId()]['nb'] < $nbpacks) {
                $packs[$pack->getId()]['nb'] = $nbpacks;
            }
        }

        $packs =  array_values($packs);
        usort($packs, function ($arr1 , $arr2) {
            $pack1 = $arr1['pack'];
            $pack2 = $arr2['pack'];
            $cycle1 = $pack1->getCycle();
            $cycle2 = $pack2->getCycle();
            if ($cycle1->getPosition() > $cycle2->getPosition()) {
                return 1;
            } else if ($cycle1->getPosition() < $cycle2->getPosition()) {
                return -1;
            }

            if ($pack1->getPosition() > $pack2->getPosition()) {
                return 1;
            } else if ($pack1->getPosition() < $pack2->getPosition()) {
                return -1;
            }
            return 0;
        });
        return $packs;
    }

    public function getSlotsByType()
    {
        $slotsByType = ['plot' => [], 'character' => [], 'location' => [], 'attachment' => [], 'event' => []];
        foreach ($this->slots as $slot) {
            if (array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
                $slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
            }
        }
        return $slotsByType;
    }

    /**
     * Sorting callback.
     * @param SlotInterface $s1
     * @param SlotInterface $s2
     * @return int
     */
    public function sortByCardCode(SlotInterface $s1, SlotInterface $s2)
    {
        return intval($s1->getCard()->getCode(), 10) - intval($s2->getCard()->getCode(), 10);
    }

    public function getSlotsByCycleOrder()
    {
        $slots_array = [];
        foreach ($this->slots as $slot){
            $slots_array[] = $slot;
        }

        usort($slots_array, array($this, "sortByCardCode"));
        $cycles = [];
        foreach ($slots_array as $slot){
            $cycles[$slot->getCard()->getPack()->getCycle()->getName()][] = $slot;
        }
        return $cycles;
    }

    public function getCountByType()
    {
        $countByType = ['character' => 0, 'location' => 0, 'attachment' => 0, 'event' => 0];
        foreach ($this->slots as $slot) {
            if (array_key_exists($slot->getCard()->getType()->getCode(), $countByType)) {
                $countByType[$slot->getCard()->getType()->getCode()] += $slot->getQuantity();
            }
        }
        return $countByType;
    }

    public function getPlotDeck()
    {
        $plotDeck = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === 'plot') {
                $plotDeck[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($plotDeck));
    }

    public function getAgendas()
    {
        $agendas = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === 'agenda') {
                $agendas[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($agendas));
    }

    public function isAlliance()
    {
        foreach ($this->getAgendas() as $agenda) {
            if ($agenda->getCard()->getCode() === '06018') {
                return true;
            }
        }
        return false;
    }
    
    public function getDrawDeck()
    {
        $drawDeck = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === 'character' || $slot->getCard()->getType()->getCode() === 'location' || $slot->getCard()->getType()->getCode() === 'attachment' || $slot->getCard()->getType()->getCode() === 'event') {
                $drawDeck[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($drawDeck));
    }

    public function filterByFaction($faction_code)
    {
        $slots = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getFaction()->getCode() === $faction_code) {
                $slots[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($slots));
    }

    public function filterByType($type_code)
    {
        $slots = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === $type_code) {
                $slots[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($slots));
    }

    public function filterByTrait($trait)
    {
        $slots = [];
        foreach ($this->slots as $slot) {
            if (preg_match("/$trait\\./", $slot->getCard()->getTraits())) {
                $slots[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($slots));
    }

    public function getCopiesAndDeckLimit()
    {
        $copiesAndDeckLimit = [];
        foreach ($this->slots as $slot) {
            $cardName = $slot->getCard()->getName();
            if (!key_exists($cardName, $copiesAndDeckLimit)) {
                $copiesAndDeckLimit[$cardName] = [
                    'copies' => $slot->getQuantity(),
                    'deck_limit' => $slot->getCard()->getDeckLimit(),
                ];
            } else {
                $copiesAndDeckLimit[$cardName]['copies'] += $slot->getQuantity();
                $copiesAndDeckLimit[$cardName]['deck_limit'] = min($slot->getCard()->getDeckLimit(), $copiesAndDeckLimit[$cardName]['deck_limit']);
            }
        }
        return $copiesAndDeckLimit;
    }

    public function getSlots()
    {
        return $this->slots;
    }

    public function getContent()
    {
        $arr = array();
        foreach ($this->slots as $slot) {
            $arr [$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        ksort($arr);
        return $arr;
    }

    public function isLegalForMelee()
    {
        $slots = $this->getSlots()->getValues();
        $cardCodes = [];
        /**
         * @var Decklistslot $slot;
         */
        foreach ($slots as $slot) {
            $cardCodes[] = $slot->getCard()->getCode();
        }
        return $this->restrictedListChecker->isLegalForMelee($cardCodes);
    }

    public function isLegalForJoust()
    {
        $slots = $this->getSlots()->getValues();
        $cardCodes = [];
        /**
         * @var Decklistslot $slot;
         */
        foreach ($slots as $slot) {
            $cardCodes[] = $slot->getCard()->getCode();
        }
        return $this->restrictedListChecker->isLegalForJoust($cardCodes);
    }
}
