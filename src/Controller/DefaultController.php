<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\DecklistInterface;
use App\Entity\Faction;
use App\Entity\Restriction;
use App\Entity\RestrictionInterface;
use App\Entity\Type;
use App\Entity\TypeInterface;
use App\Services\AgendaHelper;
use App\Services\CardsData;
use App\Services\DecklistManager;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultController extends AbstractController
{
    use LocaleAwareTemplating;

    /**
     * @Route("/", name="index", methods={"GET"})
     *
     * @param int $cacheExpiration
     * @param string $gameName
     * @param string $publisherName
     * @param DecklistManager $decklistManager
     * @param AgendaHelper $agendaHelper
     * @return Response
     * @throws Exception
     */
    public function indexAction(
        int $cacheExpiration,
        string $gameName,
        string $publisherName,
        DecklistManager $decklistManager,
        AgendaHelper $agendaHelper
    ) {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $decklistManager->setLimit(1);

        $typeNames = [];
        /* @var TypeInterface $type */
        foreach ($this->getDoctrine()->getRepository(Type::class)->findAll() as $type) {
            $typeNames[$type->getCode()] = $type->getName();
        }

        $decklists_by_faction = [];
        $factions = $this->getDoctrine()
            ->getRepository(Faction::class)
            ->findBy(['isPrimary' => true], ['code' => 'ASC']);

        foreach ($factions as $faction) {
            $array = [];
            $array['faction'] = $faction;

            $decklistManager->setFaction($faction);
            $paginator = $decklistManager->findDecklistsByPopularity();
            /* @var DecklistInterface $decklist */
            $decklist = $paginator->getIterator()->current();

            if ($decklist) {
                $array['decklist'] = $decklist;

                $countByType = $decklist->getSlots()->getCountByType();
                $counts = [];
                foreach ($countByType as $code => $qty) {
                    $typeName = $typeNames[$code];
                    $counts[] = $qty." ".$typeName."s";
                }
                $array['count_by_type'] = join(' &bull; ', $counts);

                $factions = [$faction->getName()];
                foreach ($decklist->getSlots()->getAgendas() as $agenda) {
                    $minor_faction = $agendaHelper->getMinorFaction($agenda->getCard());
                    if ($minor_faction) {
                        $factions[] = $minor_faction->getName();
                    } elseif ($agenda->getCard()->getCode() != '06018') { // prevent Alliance agenda to show up
                        $factions[] = $agenda->getCard()->getName();
                    }
                }
                $array['factions'] = join(' / ', $factions);

                $decklists_by_faction[] = $array;
            }
        }

        return $this->render(
            'Default/index.html.twig',
            [
                'pagetitle' => "${gameName} Deckbuilder",
                'pagedescription' => "Build your deck for ${gameName} by ${publisherName}."
                    ." Browse the cards and the thousand of decklists submitted by the community."
                    ." Publish your own decks and get feedback.",
                'decklists_by_faction' => $decklists_by_faction,
            ],
            $response
        );
    }

    /**
     * @Route("/rulesreference", name="rulesreference", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function rulesreferenceAction(int $cacheExpiration, TranslatorInterface $translator)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $page = $this->renderView(
            'Default/rulesreference.html.twig',
            array("pagetitle" => $translator->trans("nav.rules"), "pagedescription" => "Rules Reference")
        );
        $response->setContent($page);

        return $response;
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function faqAction(int $cacheExpiration, TranslatorInterface $translator)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $page = $this->renderView(
            'Default/faq.html.twig',
            array("pagetitle" => $translator->trans("nav.rules"), "pagedescription" => "F.A.Q")
        );
        $response->setContent($page);

        return $response;
    }

    /**
     * @Route("/restrictions", name="restrictions", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function restrictionsAction(
        int $cacheExpiration,
        TranslatorInterface $translator,
        CardsData $cardsData
    ): Response {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $restrictionsRepo = $this->getDoctrine()->getRepository(Restriction::class);
        $cardsRepo = $this->getDoctrine()->getRepository(Card::class);
        $restrictions = $restrictionsRepo->findBy([], ['effectiveOn' => 'DESC']);

        // get all card codes from all RLs
        $allCardCodes = [];
        foreach ($restrictions as $restriction) {
            $allCardCodes = array_merge($allCardCodes, $restriction->getReferencedCards());
        }

        // preload all cards data for those so we don't have to take multiple trips to the database.
        $cards = $cardsRepo->findBy(
            ['code' => array_unique($allCardCodes)],
            ['faction' => 'ASC', 'type' => 'ASC', 'code' => 'ASC']
        );

        // create a lookup map of cards by their code
        $cardsMap = [];
        /* @var CardInterface $card */
        foreach ($cards as $card) {
            $cardsMap[$card->getCode()] = $cardsData->getCardInfo($card, false, null);
        }

        $extractAndSortList = function (array $cardCodes, array $cardsMap): array {
            $rhett = array_values(array_intersect_key($cardsMap, array_fill_keys($cardCodes, null)));
            usort(
                $rhett,
                function (array $a, array $b): int {
                    $factions = [
                        'neutral',
                        'baratheon',
                        'greyjoy',
                        'lannister',
                        'martell',
                        'thenightswatch',
                        'stark',
                        'tyrell',
                        'targaryen',
                    ];
                    $types = [
                        'agenda',
                        'plot',
                        'character',
                        'attachment',
                        'location',
                        'event',
                    ];

                    return array_search($a['faction_code'], $factions) <=> array_search($b['faction_code'], $factions)
                        ?: array_search($a['type_code'], $types) <=> array_search($b['type_code'], $types);
                }
            );

            return $rhett;
        };

        $extractAndSortPods = function (array $pods, array $cardsMap, $extractAndSortList): array {
            return array_map(function (array $pod) use ($cardsMap, $extractAndSortList) {
                $rhett = [
                    'title' => $pod['title'],
                ];
                if (array_key_exists('restricted', $pod) && $pod['restricted']) {
                    $rhett['restricted'] = $cardsMap[$pod['restricted']];
                }
                $rhett['cards'] = $extractAndSortList($pod['cards'], $cardsMap);
                return $rhett;
            }, $pods);
        };

        // transmogrify restricted lists for output
        $restrictions = array_map(
            function (RestrictionInterface $restriction) use ($extractAndSortList, $extractAndSortPods, $cardsMap) {
                $rhett = [
                    'code' => $restriction->getcode(),
                    'cardSet' => $restriction->getCardSet(),
                    'title' => $restriction->getTitle(),
                    'effectiveOn' => $restriction->getEffectiveOn(),
                    'active' => $restriction->isActive(),
                    'issuer' => $restriction->getIssuer(),
                ];
                $rhett['joustRestrictedList'] = $extractAndSortList($restriction->getJoustRestrictedList(), $cardsMap);
                $rhett['joustRestrictedPods'] = $extractAndSortPods(
                    $restriction->getJoustRestrictedPods(),
                    $cardsMap,
                    $extractAndSortList
                );
                $rhett['joustBannedList'] = $extractAndSortList($restriction->getJoustBannedList(), $cardsMap);
                $rhett['meleeRestrictedList'] = $extractAndSortList($restriction->getMeleeRestrictedList(), $cardsMap);
                $rhett['meleeRestrictedPods'] = $extractAndSortPods(
                    $restriction->getMeleeRestrictedPods(),
                    $cardsMap,
                    $extractAndSortList
                );
                $rhett['meleeBannedList'] = $extractAndSortList($restriction->getMeleeBannedList(), $cardsMap);

                return $rhett;
            },
            $restrictions
        );


        // split RLs into active and inactive
        // and populate them with full card info
        $activeRestrictions = [];
        $inactiveRestrictions = [];

        foreach ($restrictions as $restriction) {
            if ($restriction['active']) {
                $activeRestrictions[] = $restriction;
            } else {
                $inactiveRestrictions[] = $restriction;
            }
        }


        $page = $this->renderView(
            'Default/restrictions.html.twig',
            [
                "pagetitle" => $translator->trans("nav.restrictions"),
                "pagedescription" => "Restricted and Banned Cards",
                "inactive_lists" => $inactiveRestrictions,
                "active_lists" => $activeRestrictions,
            ]
        );
        $response->setContent($page);

        return $response;
    }

    /**
     * @Route("/tournamentregulations", name="tournamentregulations", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function tournamentregulationsAction(int $cacheExpiration, TranslatorInterface $translator)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $page = $this->renderView(
            'Default/tournamentregulations.html.twig',
            array(
                "pagetitle" => $translator->trans("nav.rules"),
                "pagedescription" => "Tournament Regulations",
            )
        );
        $response->setContent($page);

        return $response;
    }

    /**
     * @Route("/about", name="about", methods={"GET"})
     * @param Request $request
     * @param int $cacheExpiration
     * @param string $gameName
     * @return Response
     */
    public function aboutAction(Request $request, int $cacheExpiration, string $gameName)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        return $this->render(
            $this->getLocaleSpecificViewPath('about', $request->getLocale()),
            array(
                "pagetitle" => "About",
                "game_name" => $gameName,
            ),
            $response
        );
    }

    /**
     * @Route("/api/", name="api_intro", methods={"GET"}, options={"i18n" = false})
     * @param int $cacheExpiration
     * @param string $gameName
     * @param string $publisherName
     * @return Response
     */
    public function apiIntroAction(int $cacheExpiration, string $gameName, string $publisherName)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        return $this->render(
            'Default/apiIntro.html.twig',
            array(
                "pagetitle" => "API",
                "game_name" => $gameName,
                "publisher_name" => $publisherName,
            ),
            $response
        );
    }
}
