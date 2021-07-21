<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\Restriction;
use App\Entity\RestrictionInterface;
use App\Services\CardsData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RestrictionsController
 * @package App\Controller
 */
class RestrictionsController extends AbstractController
{
    use LocaleAwareTemplating;

    /**
     * @Route("/restrictions", name="restrictions", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @param CardsData $cardsData
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
                        ?: array_search($a['type_code'], $types) <=> array_search($b['type_code'], $types)
                        ?: $a['name'] <=> $b['name']
                        ?: $a['code'] <=> $b['code'];
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
            'Restrictions/index.html.twig',
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
}
