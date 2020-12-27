<?php

declare(strict_types=1);

namespace App\Services;

use App\Classes\SlotCollectionInterface;
use App\Entity\CommonDeckInterface;
use App\Entity\RestrictionInterface;
use App\Entity\SlotInterface;

/**
 * Class RestrictionsChecker
 * Checks a given deck against a given Restricted List for tournament legality.
 * @package App\Services
 */
class RestrictionsChecker
{
    /**
     * @param RestrictionInterface $restriction
     * @param CommonDeckInterface $deck
     * @return bool
     */
    public function isLegalForJoust(RestrictionInterface $restriction, CommonDeckInterface $deck): bool
    {

        $cardsInDeck = $this->getCardsInDeck($deck->getSlots());
        return ! $this->isBannedForJoust($restriction, $cardsInDeck)
            && ! $this->isRestrictedForJoust($restriction, $cardsInDeck);
    }

    /**
     * @param RestrictionInterface $restriction
     * @param CommonDeckInterface $deck
     * @return bool
     */
    public function isLegalForMelee(RestrictionInterface $restriction, CommonDeckInterface $deck): bool
    {
        $cardsInDeck = $this->getCardsInDeck($deck->getSlots());
        return ! $this->isBannedForMelee($restriction, $cardsInDeck)
            && ! $this->isRestrictedForMelee($restriction, $cardsInDeck);
    }

    /**
     * Returns a list of codes for all cards in the deck.
     * @param SlotCollectionInterface $slots
     * @return array
     */
    protected function getCardsInDeck(SlotCollectionInterface $slots): array
    {
        $codes = [];
        /* @var SlotInterface $slot; */
        foreach ($slots as $slot) {
            $codes[] = $slot->getCard()->getCode();
        }
        return $codes;
    }

    /**
     * @param RestrictionInterface $restriction
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isRestrictedForJoust(RestrictionInterface $restriction, array $cardsInDeck): bool
    {
        return $this->isRestricted(
            $restriction->getJoustRestrictedList(),
            $restriction->getJoustRestrictedPods(),
            $cardsInDeck
        );
    }

    /**
     * @param RestrictionInterface $restriction
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isBannedForJoust(RestrictionInterface $restriction, array $cardsInDeck): bool
    {
        return $this->isBanned($restriction->getJoustBannedList(), $cardsInDeck);
    }

    /**
     * @param RestrictionInterface $restriction
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isRestrictedForMelee(RestrictionInterface $restriction, array $cardsInDeck): bool
    {
        return $this->isRestricted(
            $restriction->getMeleeRestrictedList(),
            $restriction->getMeleeRestrictedPods(),
            $cardsInDeck
        );
    }

    /**
     * @param RestrictionInterface $restriction
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isBannedForMelee(RestrictionInterface $restriction, array $cardsInDeck): bool
    {
        return $this->isBanned($restriction->getMeleeBannedList(), $cardsInDeck);
    }

    /**
     * @param array $restrictedCards
     * @param array $pods
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isRestricted(array $restrictedCards, array $pods, array $cardsInDeck): bool
    {
        if (empty($restrictedCards) && empty($pods)) {
            return false;
        }

        // a deck cannot have more than one of its cards on a restricted list
        $restrictedCardsInDeck = array_intersect($cardsInDeck, $restrictedCards);
        if (1 < count($restrictedCardsInDeck)) {
            return true;
        }

        // a deck cannot include cards from pods if the pod's restricted card is part of the deck.
        $isRestrictedInPod = false;
        foreach ($pods as $pod) {
            $restricted = $pod['restricted'];
            if (! in_array($restricted, $cardsInDeck)) {
                continue;
            }
            if (array_intersect($pod['cards'], $cardsInDeck)) {
                $isRestrictedInPod = true;
                break;
            }
        }
        return $isRestrictedInPod;
    }

    /**
     * @param array $bannedCards
     * @param array $cardsInDeck
     * @return bool
     */
    protected function isBanned(array $bannedCards, array $cardsInDeck): bool
    {
        if (empty($bannedCards)) {
            return false;
        }

        // none of the cards in the deck can be on the banned list
        return ! empty(array_intersect($cardsInDeck, $bannedCards));
    }
}
