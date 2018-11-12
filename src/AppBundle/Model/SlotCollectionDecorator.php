<?php

namespace AppBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Decorator for a collection of SlotInterface
 */
class SlotCollectionDecorator implements \AppBundle\Model\SlotCollectionInterface
{
    protected $slots;

    public function __construct(\Doctrine\Common\Collections\Collection $slots)
    {
        $this->slots = $slots;
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
            if (!isset($packs[$pack->getPosition()])) {
                $packs[$pack->getPosition()] = [
                    'pack' => $pack,
                    'nb' => 0
                ];
            }

            $nbpacks = ceil($slot->getQuantity() / $card->getQuantity());
            if ($packs[$pack->getPosition()]['nb'] < $nbpacks) {
                $packs[$pack->getPosition()]['nb'] = $nbpacks;
            }
        }
        ksort($packs);
        return array_values($packs);
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

    public static function sortByCycleOrder($s1, $s2)
    {
        return intval($s1->getCard()->getCode()) - intval($s2->getCard()->getCode());
    }

    public static function sortByCycleOrderDeluxeAfter($s1, $s2)
    {
        // Compare cycle first. Cycle size should be enough for the moment. Core set will be first anyway.

        // If at least one is a core set slot, then ordering by code will be fine
        $comparingCore = $s1->getCard()->getPack()->getCycle()->getPosition() == 1 || $s2->getCard()->getPack()->getCycle()->getPosition() == 1;

        // If the packs have same size (both non-deluxe, or both deluxe), then ordering by code will be fine
        $sameSize = $s1->getCard()->getPack()->getCycle()->getSize() == $s2->getCard()->getPack()->getCycle()->getSize();


        if ($comparingCore || $sameSize)
            // Normal ordering
            return intval($s1->getCard()->getCode()) - intval($s2->getCard()->getCode());
        else
            // Cycle with size 6 (non-deluxe) should be first
            return intval($s2->getCard()->getPack()->getCycle()->getSize()) - intval($s1->getCard()->getPack()->getCycle()->getSize());
    }


    public function getSlotsByCycleOrder($deluxeAfter)
    {
        $slots_array = [];
        foreach ($this->slots as $slot){
            $slots_array[] = $slot;
        }

        if (!$deluxeAfter)
            usort($slots_array, array("AppBundle\Model\SlotCollectionDecorator", "sortByCycleOrder"));
        else
            usort($slots_array, array("AppBundle\Model\SlotCollectionDecorator", "sortByCycleOrderDeluxeAfter"));

        // At this point, $slots_array is also ordered by cycle
        $slots_array_by_cicle = [];
        foreach ($slots_array as $slot){
            $slots_array_by_cicle[$slot->getCard()->getPack()->getCycle()->getName()][] = $slot;
        }
        return $slots_array_by_cicle;
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
}
