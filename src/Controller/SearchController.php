<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\Cycle;
use App\Entity\Faction;
use App\Entity\Pack;
use App\Entity\PackInterface;
use App\Entity\Type;
use App\Services\CardsData;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\Controller
 */
class SearchController extends AbstractController
{
    /**
     * @var string[] $searchKeys
     * @todo redeclare as constant [ST 2020/08/09]
     */
    public static array $searchKeys = array(
            ''  => 'code',
            'a' => 'flavor',
            'b' => 'claim',
            'c' => 'cycle',
            'd' => 'designer',
            'e' => 'pack',
            'f' => 'faction',
            'g' => 'isIntrigue',
            'h' => 'reserve',
            'i' => 'illustrator',
            'k' => 'traits',
            'l' => 'isLoyal',
            'm' => 'isMilitary',
            'n' => 'income',
            'o' => 'cost',
            'p' => 'isPower',
            'r' => 'date_release',
            's' => 'strength',
            't' => 'type',
            'u' => 'isUnique',
            'v' => 'initiative',
            'x' => 'text',
            'y' => 'quantity',
    );

    /**
     * @var string[] $searchTypes
     * @todo redeclare as constant [ST 2020/08/09]
     */
    public static array $searchTypes = array(
        't' => 'code',
        'e' => 'code',
        'f' => 'code',
        '' => 'string',
        'a' => 'string',
        'i' => 'string',
        'd' => 'string',
        'k' => 'string',
        'r' => 'string',
        'x' => 'string',
        'b' => 'integer',
        'c' => 'integer',
        'h' => 'integer',
        'n' => 'integer',
        'o' => 'integer',
        's' => 'integer',
        'v' => 'integer',
        'y' => 'integer',
        'g' => 'boolean',
        'l' => 'boolean',
        'm' => 'boolean',
        'p' => 'boolean',
        'u' => 'boolean',
    );

    /**
     * @Route("/search", name="cards_search", methods={"GET"})
     * @param int $cacheExpiration
     * @param CardsData $cardsData
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function formAction(int $cacheExpiration, CardsData $cardsData, TranslatorInterface $translator)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $dbh = $this->getDoctrine()->getConnection();

        $packs = $cardsData->allsetsdata();

        $cycles = $this->getDoctrine()->getRepository(Cycle::class)->findAll();
        $types = $this->getDoctrine()->getRepository(Type::class)->findAll();
        $factions = $this->getDoctrine()->getRepository(Faction::class)->findAllAndOrderByName();

        $list_traits = $this->getDoctrine()->getRepository(Card::class)->findTraits();
        $traits = [];
        foreach ($list_traits as $card) {
            $subs = explode('.', $card["traits"]);
            foreach ($subs as $sub) {
                $traits[trim($sub)] = 1;
            }
        }
        $traits = array_filter(array_keys($traits));
        sort($traits);

        $list_illustrators = $dbh->executeQuery(
            "SELECT DISTINCT c.illustrator FROM card c WHERE c.illustrator != '' ORDER BY c.illustrator"
        )->fetchAll();
        $illustrators = array_map(function ($card) {
            return $card["illustrator"];
        }, $list_illustrators);

        return $this->render('Search/searchform.html.twig', array(
                "pagetitle" => $translator->trans('search.title'),
                "pagedescription" => "Find all the cards of the game, easily searchable.",
                "packs" => $packs,
                "cycles" => $cycles,
                "types" => $types,
                "factions" => $factions,
                "traits" => $traits,
                "illustrators" => $illustrators,
        ), $response);
    }

    /**
     * @Route("/card/{card_code}", name="cards_zoom", methods={"GET"}, requirements={"card_code"="\d+"})
     * @param string $card_code
     * @param Request $request
     * @param string $gameName
     * @param string $publisherName
     * @return Response
     * @throws NonUniqueResultException
     */
    public function zoomAction($card_code, Request $request, string $gameName, string $publisherName)
    {
        /* @var CardInterface $card */
        $card = $this->getDoctrine()->getRepository(Card::class)->findByCode($card_code);
        if (!$card) {
            throw $this->createNotFoundException('Sorry, this card is not in the database (yet?)');
        }

        $meta = $card->getName()
             . ", a "
            . $card->getFaction()->getName()
            . " "
            . $card->getType()->getName()
            . " card for ${gameName} from the set "
            . $card->getPack()->getName()
            . " published by ${publisherName}.";

        return $this->forward(
            'App\Controller\SearchController:displayAction',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $card->getCode(),
                'view' => 'card',
                'sort' => 'set',
                'pagetitle' => $card->getName(),
                'meta' => $meta
            )
        );
    }

    /**
     * @Route(
     *     "/set/{pack_code}/{view}/{sort}/{page}",
     *     name="cards_list",
     *     methods={"GET"},
     *     defaults={"view"="list", "sort"="set", "page"=1}
     * )
     * @param string $pack_code
     * @param string $view
     * @param string $sort
     * @param int $page
     * @param Request $request
     * @param string $gameName
     * @param string $publisherName
     * @return Response
     */
    public function listAction(
        $pack_code,
        $view,
        $sort,
        $page,
        Request $request,
        string $gameName,
        string $publisherName
    ) {
        /* @var PackInterface $pack */
        $pack = $this->getDoctrine()->getRepository(Pack::class)->findByCode($pack_code);
        if (!$pack) {
            throw $this->createNotFoundException('This pack does not exist');
        }


        $meta = $pack->getName().", a set of cards for ${gameName}"
                .($pack->getDateRelease() ? " published on ".$pack->getDateRelease()->format('Y/m/d') : "")
                ." by ${publisherName}.";

        $key = array_search('pack', SearchController::$searchKeys);

        return $this->forward(
            'App\Controller\SearchController:displayAction',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $key.':'.$pack_code,
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                'pagetitle' => $pack->getName(),
                'meta' => $meta,
            )
        );
    }

    /**
     * @Route(
     *     "/cycle/{cycle_code}/{view}/{sort}/{page}",
     *     name="cards_cycle",
     *     methods={"GET"},
     *     defaults={"view"="list", "sort"="faction", "page"=1}
     * )
     * @param string $cycle_code
     * @param string $view
     * @param string $sort
     * @param int $page
     * @param Request $request
     * @param string $gameName
     * @param string $publisherName
     * @return Response
     */
    public function cycleAction(
        $cycle_code,
        $view,
        $sort,
        $page,
        Request $request,
        string $gameName,
        string $publisherName
    ) {
        $cycle = $this->getDoctrine()->getRepository(Cycle::class)->findOneBy(array("code" => $cycle_code));
        if (!$cycle) {
            throw $this->createNotFoundException('This cycle does not exist');
        }

        $meta = $cycle->getName().", a cycle of datapack for ${gameName} published by ${publisherName}.";

        $key = array_search('cycle', SearchController::$searchKeys);

        return $this->forward(
            'App\Controller\SearchController:displayAction',
            array(
                '_route' => $request->attributes->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                'q' => $key.':'.$cycle->getPosition(),
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                'pagetitle' => $cycle->getName(),
                'meta' => $meta,
            )
        );
    }

    /**
     * @Route("/process", name="cards_processSearchForm", methods={"GET"})
     * Processes the action of the card search form
     * @param Request $request
     * @return RedirectResponse
     */
    public function processAction(Request $request)
    {
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';

        $operators = array(":","!","<",">");
        $factions = $this->getDoctrine()->getRepository(Faction::class)->findAll();

        $params = [];
        if ($request->query->get('q') != "") {
            $params[] = $request->query->get('q');
        }
        foreach (SearchController::$searchKeys as $key => $searchName) {
            $val = $request->query->get($key);
            if (isset($val) && $val != "") {
                if (is_array($val)) {
                    if ($searchName == "faction" && count($val) == count($factions)) {
                        continue;
                    }
                    $params[] = $key.":".implode("|", array_map(function ($s) {
                        return strstr($s, " ") !== false ? "\"$s\"" : $s;
                    }, $val));
                } else {
                    if ($searchName == "date_release") {
                        $op = "";
                    } else {
                        if (!preg_match('/^[\p{L}\p{N}\_\-\&]+$/u', $val, $match)) {
                            $val = "\"$val\"";
                        }
                        $op = $request->query->get($key."o");
                        if (!in_array($op, $operators)) {
                            $op = ":";
                        }
                    }
                    $params[] = "$key$op$val";
                }
            }
        }
        $find = array('q' => implode(" ", $params));
        if ($sort != "name") {
            $find['sort'] = $sort;
        }
        if ($view != "list") {
            $find['view'] = $view;
        }
        return $this->redirect($this->generateUrl('cards_find').'?'.http_build_query($find));
    }

    /**
     * Processes the action of the single card search input
     * @Route("/find", name="cards_find", methods={"GET"})
     * @param Request $request
     * @param CardsData $cardsData
     * @param RouterInterface $router
     * @return RedirectResponse|Response
     */
    public function findAction(Request $request, CardsData $cardsData, RouterInterface $router)
    {
        $q = $request->query->get('q');
        $page = $request->query->get('page') ?: 1;
        $view = $request->query->get('view') ?: 'list';
        $sort = $request->query->get('sort') ?: 'name';

        // we may be able to redirect to a better url if the search is on a single set
        $conditions = $cardsData->syntax($q);
        if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
            if ($conditions[0][0] == array_search('pack', SearchController::$searchKeys)) {
                $url = $router->generate(
                    'cards_list',
                    array(
                        'pack_code' => $conditions[0][2],
                        'view' => $view,
                        'sort' => $sort,
                        'page' => $page
                    )
                );
                return $this->redirect($url);
            }
            if ($conditions[0][0] == array_search('cycle', SearchController::$searchKeys)) {
                $cycle_position = $conditions[0][2];
                $cycle = $this->getDoctrine()
                    ->getRepository(Cycle::class)
                    ->findOneBy(array('position' => $cycle_position));
                if ($cycle) {
                    $url = $router->generate(
                        'cards_cycle',
                        array(
                            'cycle_code' => $cycle->getCode(),
                            'view' => $view,
                            'sort' => $sort,
                            'page' => $page
                        )
                    );
                    return $this->redirect($url);
                }
            }
        }

        return $this->forward(
            'App\Controller\SearchController:displayAction',
            array(
                'q' => $q,
                'view' => $view,
                'sort' => $sort,
                'page' => $page,
                '_route' => $request->get('_route'),
                '_route_params' => $request->attributes->get('_route_params'),
                '_get_params' => $request->query->all()
            )
        );
    }

    /**
     * @todo Replace this forwarding target with non-action helper method. [ST 2020/08/09]
     * @param string $cacheExpiration
     * @param CardsData $cardsData
     * @param RouterInterface $router
     * @param $q
     * @param $sort
     * @param string $view
     * @param int $page
     * @param string $pagetitle
     * @param string $meta
     * @return Response|null
     * @throws Exception
     */
    public function displayAction(
        string $cacheExpiration,
        CardsData $cardsData,
        RouterInterface $router,
        $q,
        $sort,
        $view = "card",
        $page = 1,
        $pagetitle = "",
        $meta = ""
    ) {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        static $availability = [];

        $cards = [];
        $first = 0;
        $last = 0;
        $pagination = '';

        $pagesizes = array(
            'list' => 240,
            'spoiler' => 240,
            'card' => 20,
            'scan' => 20,
            'short' => 1000,
        );
        $includeReviews = false;

        if (!array_key_exists($view, $pagesizes)) {
            $view = 'list';
        }

        $conditions = $cardsData->syntax($q);
        $conditions = $cardsData->validateConditions($conditions);

        // reconstruction de la bonne chaine de recherche pour affichage
        $q = $cardsData->buildQueryFromConditions($conditions);
        if ($q && $rows = $cardsData->getSearchRows($conditions, $sort)) {
            if (count($rows) === 1 && ! $rows[0]->getPack()->getWorkInProgress()) {
                $includeReviews = true;
            }

            if ($pagetitle == "") {
                if (count($conditions) == 1 && count($conditions[0]) == 3 && $conditions[0][1] == ":") {
                    if ($conditions[0][0] == "e") {
                        $pack = $this->getDoctrine()
                            ->getRepository(Pack::class)
                            ->findOneBy(array("code" => $conditions[0][2]));
                        if ($pack) {
                            $pagetitle = $pack->getName();
                        }
                    }
                    if ($conditions[0][0] == "c") {
                        $cycle = $this->getDoctrine()
                            ->getRepository(Cycle::class)
                            ->findOneBy(array("code" => $conditions[0][2]));
                        if ($cycle) {
                            $pagetitle = $cycle->getName();
                        }
                    }
                }
            }


            // calcul de la pagination
            $nb_per_page = $pagesizes[$view];
            $first = $nb_per_page * ($page - 1);
            if ($first > count($rows)) {
                $first = 0;
            }
            $last = $first + $nb_per_page;

            // data à passer à la view
            for ($rowindex = $first; $rowindex < $last && $rowindex < count($rows); $rowindex++) {
                $card = $rows[$rowindex];
                $pack = $card->getPack();
                $cardinfo = $cardsData->getCardInfo($card, false, null);
                if (empty($availability[$pack->getCode()])) {
                    $availability[$pack->getCode()] = false;
                    if ($pack->getDateRelease() && $pack->getDateRelease() <= new DateTime()) {
                        $availability[$pack->getCode()] = true;
                    }
                }
                $cardinfo['available'] = $availability[$pack->getCode()];
                if ($includeReviews) {
                    $cardinfo['reviews'] = $cardsData->getReviews($card);
                }
                $cards[] = $cardinfo;
            }

            $first += 1;

            // si on a des cartes on affiche une bande de navigation/pagination
            if (count($rows)) {
                if (count($rows) == 1) {
                    $pagination = $this->setnavigation($router, $card);
                } else {
                    $pagination = $this->pagination($router, $nb_per_page, count($rows), $first, $q, $view, $sort);
                }
            }

            // si on est en vue "short" on casse la liste par tri
            if (count($cards) && $view == "short") {
                $sortfields = array(
                    'set' => 'pack_name',
                    'name' => 'name',
                    'faction' => 'faction_name',
                    'type' => 'type_name',
                    'cost' => 'cost',
                    'strength' => 'strength',
                );

                $brokenlist = [];
                for ($i=0; $i<count($cards); $i++) {
                    $val = $cards[$i][$sortfields[$sort]];
                    if ($sort == "name") {
                        $val = substr($val, 0, 1);
                    }
                    if (!isset($brokenlist[$val])) {
                        $brokenlist[$val] = [];
                    }
                    array_push($brokenlist[$val], $cards[$i]);
                }
                $cards = $brokenlist;
            }
        }

        $searchbar = $this->renderView('Search/searchbar.html.twig', array(
            "q" => $q,
            "view" => $view,
            "sort" => $sort,
        ));

        if (empty($pagetitle)) {
            $pagetitle = $q;
        }

        // attention si $s="short", $cards est un tableau à 2 niveaux au lieu de 1 seul
        return $this->render('Search/display-'.$view.'.html.twig', array(
            "view" => $view,
            "sort" => $sort,
            "cards" => $cards,
            "first"=> $first,
            "last" => $last,
            "searchbar" => $searchbar,
            "pagination" => $pagination,
            "pagetitle" => $pagetitle,
            "metadescription" => $meta,
            "includeReviews" => $includeReviews,
        ), $response);
    }

    /**
     * @param RouterInterface $router
     * @param CardInterface $card
     * @return string
     * @throws NonUniqueResultException
     */
    protected function setnavigation(RouterInterface $router, CardInterface $card)
    {
        $repo = $this->getDoctrine()->getRepository(Card::class);
        $prev = $repo->findPreviousCard($card);
        $next = $repo->findNextCard($card);
        return $this->renderView('Search/setnavigation.html.twig', array(
                "prevtitle" => $prev ? $prev->getName() : "",
                "prevhref" => $prev ? $router->generate('cards_zoom', ['card_code' => $prev->getCode()]) : "",
                "nexttitle" => $next ? $next->getName() : "",
                "nexthref" => $next ? $router->generate('cards_zoom', ['card_code' => $next->getCode()]) : "",
                "settitle" => $card->getPack()->getName(),
                "sethref" => $router->generate('cards_list', ['pack_code' => $card->getPack()->getCode()]),
        ));
    }

    /**
     * @param RouterInterface $router
     * @param $q
     * @param $v
     * @param $s
     * @param $ps
     * @param $pi
     * @param $total
     * @return string
     */
    protected function paginationItem(RouterInterface $router, $q, $v, $s, $ps, $pi, $total)
    {
        return $this->renderView('Search/paginationitem.html.twig', array(
            "href" => $q == null ? "" : $router
                ->generate('cards_find', array('q' => $q, 'view' => $v, 'sort' => $s, 'page' => $pi)),
            "ps" => $ps,
            "pi" => $pi,
            "s" => $ps*($pi-1)+1,
            "e" => min($ps*$pi, $total),
        ));
    }

    /**
     * @param RouterInterface $router
     * @param $pagesize
     * @param $total
     * @param $current
     * @param $q
     * @param $view
     * @param $sort
     * @return string
     */
    protected function pagination(RouterInterface $router, $pagesize, $total, $current, $q, $view, $sort)
    {
        if ($total < $pagesize) {
            $pagesize = $total;
        }

        $pagecount = ceil($total / $pagesize);
        $pageindex = ceil($current / $pagesize); #1-based

        $first = "";
        if ($pageindex > 2) {
            $first = $this->paginationItem($router, $q, $view, $sort, $pagesize, 1, $total);
        }

        $prev = "";
        if ($pageindex > 1) {
            $prev = $this->paginationItem($router, $q, $view, $sort, $pagesize, $pageindex - 1, $total);
        }

        $current = $this->paginationItem($router, null, $view, $sort, $pagesize, $pageindex, $total);

        $next = "";
        if ($pageindex < $pagecount) {
            $next = $this->paginationItem($router, $q, $view, $sort, $pagesize, $pageindex + 1, $total);
        }

        $last = "";
        if ($pageindex < $pagecount - 1) {
            $last = $this->paginationItem($router, $q, $view, $sort, $pagesize, $pagecount, $total);
        }

        return $this->renderView('Search/pagination.html.twig', array(
            "first" => $first,
            "prev" => $prev,
            "current" => $current,
            "next" => $next,
            "last" => $last,
            "count" => $total,
            "ellipsisbefore" => $pageindex > 3,
            "ellipsisafter" => $pageindex < $pagecount - 2,
        ));
    }
}
