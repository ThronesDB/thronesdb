<?php

namespace App\Services;

use App\Entity\Card;
use App\Entity\Deckchange;
use App\Entity\DeckInterface;
use App\Entity\Decklist;
use App\Entity\DecklistInterface;
use App\Entity\Deckslot;
use App\Entity\FactionInterface;
use App\Entity\PackInterface;
use App\Entity\UserInterface;
use App\Helper\AgendaHelper;
use App\Helper\DeckValidationHelper;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bridge\Monolog\Logger;

class DeckManager
{
    /**
     * @var EntityManager
     */
    protected $doctrine;
    /**
     * @var DeckValidationHelper
     */
    protected $deck_validation_helper;
    /**
     * @var AgendaHelper
     */
    protected $agenda_helper;
    /**
     * @var Diff
     */
    protected $diff;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(
        EntityManager $doctrine,
        DeckValidationHelper $deck_validation_helper,
        AgendaHelper $agenda_helper,
        Diff $diff,
        Logger $logger
    ) {
        $this->doctrine = $doctrine;
        $this->deck_validation_helper = $deck_validation_helper;
        $this->agenda_helper = $agenda_helper;
        $this->diff = $diff;
        $this->logger = $logger;
    }

    /**
     * @param UserInterface $user
     * @return Collection
     * @see UserInterface::getDecks()
     */
    public function getByUser(UserInterface $user)
    {
        return $user->getDecks();
    }

    /**
     * @param UserInterface $user
     * @param DeckInterface $deck
     * @param int $decklist_id
     * @param string $name
     * @param FactionInterface $faction
     * @param string $description
     * @param string|array $tags
     * @param array $content
     * @param DeckInterface $source_deck
     * @return int
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function save($user, $deck, $decklist_id, $name, $faction, $description, $tags, $content, $source_deck)
    {
        $deck_content = [];

        if ($decklist_id) {
            /* @var DecklistInterface $decklist */
            $decklist = $this->doctrine->getRepository(Decklist::class)->find($decklist_id);
            if ($decklist) {
                $deck->setParent($decklist);
            }
        }

        $deck->setName($name);
        $deck->setFaction($faction);
        $deck->setDescriptionMd($description);
        $deck->setUser($user);
        $deck->setMinorVersion($deck->getMinorVersion() + 1);
        $cards = [];
        /* @var PackInterface $latestPack */
        $latestPack = null;
        foreach ($content as $card_code => $qty) {
            $card = $this->doctrine->getRepository(Card::class)->findOneBy(array(
                "code" => $card_code
            ));
            if (!$card) {
                continue;
            }

            $cards [$card_code] = $card;

            $pack = $card->getPack();
            if (!$latestPack) {
                $latestPack = $pack;
            } elseif (empty($pack->getDateRelease())) {
                $latestPack = $pack;
            } elseif ($latestPack->getDateRelease() < $pack->getDateRelease()) {
                $latestPack = $pack;
            }
        }
        $deck->setLastPack($latestPack);
        if (empty($tags)) {
            // tags can never be empty. if it is we put faction in
            $tags = [$faction->getCode()];
        }
        if (is_string($tags)) {
            $tags = preg_split('/\s+/', $tags);
        }
        $tags = implode(' ', array_unique(array_values($tags)));
        $deck->setTags($tags);
        $this->doctrine->persist($deck);

        // on the deck content

        if ($source_deck) {
            // compute diff between current content and saved content
            list($listings) = $this->diff->diffContents(array(
                $content,
                $source_deck->getSlots()->getContent()
            ));
            // remove all change (autosave) since last deck update (changes are sorted)
            $changes = $this->getUnsavedChanges($deck);
            foreach ($changes as $change) {
                $this->doctrine->remove($change);
            }
            $this->doctrine->flush();
            // save new change unless empty
            if (count($listings [0]) || count($listings [1])) {
                $change = new Deckchange();
                $change->setDeck($deck);
                $change->setVariation(json_encode($listings));
                $change->setIsSaved(true);
                $change->setVersion($deck->getVersion());
                $this->doctrine->persist($change);
                $this->doctrine->flush();
            }
            // copy version
            $deck->setMajorVersion($source_deck->getMajorVersion());
            $deck->setMinorVersion($source_deck->getMinorVersion());
        }
        foreach ($deck->getSlots() as $slot) {
            $deck->removeSlot($slot);
            $this->doctrine->remove($slot);
        }

        foreach ($content as $card_code => $qty) {
            $card = $cards [$card_code];
            $slot = new Deckslot();
            $slot->setQuantity($qty);
            $slot->setCard($card);
            $slot->setDeck($deck);
            $deck->addSlot($slot);
            $deck_content [$card_code] = array(
                'card' => $card,
                'qty' => $qty
            );
        }

        $deck->setProblem($this->deck_validation_helper->findProblem($deck));

        return $deck->getId();
    }

    public function revert($deck)
    {
        $changes = $this->getUnsavedChanges($deck);
        foreach ($changes as $change) {
            $this->doctrine->remove($change);
        }
        // if all remaining cards are agendas, delete it
        $nonAgendaCards = 0;
        foreach ($deck->getSlots() as $slot) {
            if ($slot->getCard()->getType()->getCode() !== 'agenda') {
                $nonAgendaCards += $slot->getQuantity();
            }
        }
        if ($nonAgendaCards === 0) {
            $this->doctrine->remove($deck);
        }
        $this->doctrine->flush();
    }

    public function getUnsavedChanges($deck)
    {
        return $this->doctrine->getRepository(Deckchange::class)->findBy(array(
                    'deck' => $deck,
                    'isSaved' => false
        ));
    }
}
