<?php

namespace AppBundle\Model;

use AppBundle\Classes\RestrictedListChecker;
use AppBundle\Entity\Decklistslot;
use AppBundle\Entity\Pack;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Decorator for a collection of slots.
 */
class SlotCollectionDecorator implements SlotCollectionInterface
{
    /**
     * @var Collection
     */
    protected $slots;

    /**
     * @var RestrictedListChecker
     */
    protected $restrictedListChecker;

    /**
     * SlotCollectionDecorator constructor.
     * @param Collection $slots
     */
    public function __construct(Collection $slots)
    {
        $this->slots = $slots;
        $this->restrictedListChecker = new RestrictedListChecker();
    }

    /**
     * @inheritdoc
     */
    public function add(SlotInterface $slot)
    {
        return $this->slots->add($slot);
    }

    /**
     * @inheritdoc
     */
    public function removeElement(SlotInterface $slot)
    {
        return $this->slots->removeElement($slot);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->slots->count();
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return $this->slots->getIterator();
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->slots->offsetExists($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->slots->offsetGet($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->slots->offsetSet($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->slots->offsetUnset($offset);
    }

    /**
     * @inheritdoc
     */
    public function countCards()
    {
        $count = 0;
        foreach ($this->slots as $slot) {
            $count += $slot->getQuantity();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public function getIncludedPacks()
    {
        $packs = [];
        /** @var SlotInterface $slot */
        foreach ($this->slots as $slot) {
            $card = $slot->getCard();
            $pack = $card->getPack();
            if (!isset($packs[$pack->getId()])) {
                $packs[$pack->getId()] = [
                    'pack' => $pack,
                    'nb' => 0,
                ];
            }

            $nbpacks = ceil($slot->getQuantity() / $card->getQuantity());
            if ($packs[$pack->getId()]['nb'] < $nbpacks) {
                $packs[$pack->getId()]['nb'] = $nbpacks;
            }
        }

        $packs =  array_values($packs);
        usort($packs, function ($arr1, $arr2) {
            /** @var Pack $pack1 */
            $pack1 = $arr1['pack'];
            /** @var Pack $pack2 */
            $pack2 = $arr2['pack'];
            $cycle1 = $pack1->getCycle();
            $cycle2 = $pack2->getCycle();
            if ($cycle1->getPosition() > $cycle2->getPosition()) {
                return 1;
            } elseif ($cycle1->getPosition() < $cycle2->getPosition()) {
                return -1;
            }

            if ($pack1->getPosition() > $pack2->getPosition()) {
                return 1;
            } elseif ($pack1->getPosition() < $pack2->getPosition()) {
                return -1;
            }
            return 0;
        });
        return $packs;
    }

    /**
     * @inheritdoc
     */
    public function getSlotsByType()
    {
        $slotsByType = ['plot' => [], 'character' => [], 'location' => [], 'attachment' => [], 'event' => []];
        foreach ($this->slots as $slot) {
            if (array_key_exists($slot->getCard()->getType()->getCode(), $slotsByType)) {
                $slotsByType[$slot->getCard()->getType()->getCode()][] = $slot;
            }
        }
        foreach ($slotsByType as &$slots) {
            usort($slots, array($this, 'sortByCardName'));
        }
        return $slotsByType;
    }

    /**
     * @inheritdoc
     */
    public function getSlotsByCycleOrder()
    {
        $slots_array = [];
        foreach ($this->slots as $slot) {
            $slots_array[] = $slot;
        }

        usort($slots_array, array($this, "sortByCardCode"));
        $cycles = [];
        foreach ($slots_array as $slot) {
            $cycles[$slot->getCard()->getPack()->getCycle()->getName()][] = $slot;
        }
        return $cycles;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function isAlliance()
    {
        foreach ($this->getAgendas() as $agenda) {
            if ($agenda->getCard()->getCode() === '06018') {
                return true;
            }
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getDrawDeck()
    {
        $drawDeck = [];
        foreach ($this->slots as $slot) {
            if ($slot->getCard()->getType()->getCode() === 'character'
                || $slot->getCard()->getType()->getCode() === 'location'
                || $slot->getCard()->getType()->getCode() === 'attachment'
                || $slot->getCard()->getType()->getCode() === 'event') {
                $drawDeck[] = $slot;
            }
        }
        return new SlotCollectionDecorator(new ArrayCollection($drawDeck));
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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
                $copiesAndDeckLimit[$cardName]['deck_limit'] = min(
                    $slot->getCard()->getDeckLimit(),
                    $copiesAndDeckLimit[$cardName]['deck_limit']
                );
            }
        }
        return $copiesAndDeckLimit;
    }

    /**
     * @inheritdoc
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @inheritdoc
     */
    public function getContent()
    {
        $arr = array();
        foreach ($this->slots as $slot) {
            $arr [$slot->getCard()->getCode()] = $slot->getQuantity();
        }
        ksort($arr);
        return $arr;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * Sorting callback.
     * @param SlotInterface $s1
     * @param SlotInterface $s2
     * @return int
     */
    protected function sortByCardCode(SlotInterface $s1, SlotInterface $s2) : int
    {
        return intval($s1->getCard()->getCode(), 10) - intval($s2->getCard()->getCode(), 10);
    }

    /**
     * Sorting callback.
     * @param SlotInterface $s1
     * @param SlotInterface $s2
     * @return int
     */
    protected function sortByCardName(SlotInterface $s1, SlotInterface $s2) : int
    {
        return strcmp($s1->getCard()->getName(), $s2->getCard()->getName()) ?: $this->sortByCardCode($s1, $s2);
    }
}
