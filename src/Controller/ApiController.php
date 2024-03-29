<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\Cycle;
use App\Entity\CycleInterface;
use App\Entity\Decklist;
use App\Entity\DecklistInterface;
use App\Entity\Pack;
use App\Entity\PackInterface;
use App\Entity\Restriction;
use App\Entity\RestrictionInterface;
use App\Services\CardsData;
use DateInterval;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Exception;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @package App\Controller
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/api/public/packs/", name="api_packs", methods={"GET"}, options={"i18n" = false})
     *
     * Get the description of all the packs as an array of JSON objects.
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the Packs",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @param Request $request
     * @param RouterInterface $router
     * @param int $cacheExpiration
     * @return Response
     */
    public function listPacksAction(Request $request, RouterInterface $router, int $cacheExpiration)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $jsonp = $request->query->get('jsonp');

        $list_packs = $this->getDoctrine()->getRepository(Pack::class)->findAll();

        // check the last-modified-since header

        $lastModified = null;
        /* @var PackInterface $pack */
        foreach ($list_packs as $pack) {
            if (!$lastModified || $lastModified < $pack->getDateUpdate()) {
                $lastModified = $pack->getDateUpdate();
            }
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // build the response

        $packs = array();
        /* @var PackInterface $pack */
        foreach ($list_packs as $pack) {
            $real = count($pack->getCards());
            $max = $pack->getSize();
            $packs[] = array(
                "name" => $pack->getName(),
                "code" => $pack->getCode(),
                "position" => $pack->getPosition(),
                "cycle_position" => $pack->getCycle()->getPosition(),
                "available" => $pack->getDateRelease() ? $pack->getDateRelease()->format('Y-m-d') : '',
                "known" => intval($real),
                "total" => $max,
                "url" => $router->generate(
                    'cards_list',
                    array('pack_code' => $pack->getCode()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'dateUpdate' => $pack->getDateUpdate()->format('c'),
            );
        }

        $content = json_encode($packs);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/api/public/cycles/", name="api_cycles", methods={"GET"}, options={"i18n" = false})
     *
     * Get the description of all the cycles as an array of JSON objects.
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the Cycles",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @param Request $request
     * @param RouterInterface $router
     * @param int $cacheExpiration
     * @return Response
     */
    public function listCyclesAction(Request $request, RouterInterface $router, int $cacheExpiration)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $jsonp = $request->query->get('jsonp');

        $cycles = $this->getDoctrine()->getRepository(Cycle::class)->findAll();

        // check the last-modified-since header
        $lastModified = null;
        foreach ($cycles as $cycle) {
            if (!$lastModified || $lastModified < $cycle->getDateUpdate()) {
                $lastModified = $cycle->getDateUpdate();
            }
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // build the response
        $data = [];
        /* @var CycleInterface $cycle */
        foreach ($cycles as $cycle) {
            $packs = array_map(function (PackInterface $pack) {
                return $pack->getCode();
            }, $cycle->getPacks()->toArray());
            $data[] = array(
                "name" => $cycle->getName(),
                "code" => $cycle->getCode(),
                "position" => $cycle->getPosition(),
                "url" => $router->generate(
                    'cards_cycle',
                    array('cycle_code' => $cycle->getCode()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
                'packs' => $packs,
                'dateUpdate' => $cycle->getDateUpdate()->format('c'),
            );
        }

        $content = json_encode($data);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Get the description of a card as a JSON object.
     *
     * @Route(
     *     "/api/public/card/{card_code}.{_format}",
     *     name="api_card",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"i18n" = false}
     * )
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="One Card",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @param string $card_code
     * @param Request $request
     * @param CardsData $cardsData
     * @param int $cacheExpiration
     * @return Response|NotFoundHttpException
     */
    public function getCardAction($card_code, Request $request, CardsData $cardsData, int $cacheExpiration)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $jsonp = $request->query->get('jsonp');

        $card = $this->getDoctrine()->getRepository(Card::class)->findOneBy(array("code" => $card_code));
        if (!$card instanceof CardInterface) {
            throw $this->createNotFoundException();
        }

        // check the last-modified-since header

        $lastModified = null;
        /* @var CardInterface $card */
        if (!$lastModified || $lastModified < $card->getDateUpdate()) {
            $lastModified = $card->getDateUpdate();
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // build the response
        $card = $cardsData->getCardInfo($card, true, $version);

        $content = json_encode($card);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Get the description of all the cards as an array of JSON objects.
     *
     * @Route("/api/public/cards/", name="api_cards", methods={"GET"}, options={"i18n" = false})
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the Cards",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @param Request $request
     * @param CardsData $cardsData
     * @param int $cacheExpiration

     * @return Response
     */
    public function listCardsAction(Request $request, CardsData $cardsData, int $cacheExpiration)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*'
        ));

        $jsonp = $request->query->get('jsonp');

        $cardsRepo = $this->getDoctrine()->getRepository(Card::class);
        // check the last-modified-since header
        $lastModifiedCard = $cardsRepo->findBy([], ['dateUpdate' => 'DESC'], 1);
        if (count($lastModifiedCard)) {
            $lastModified = $lastModifiedCard[0]->getDateUpdate();
            $response->setLastModified($lastModified);
            if ($response->isNotModified($request)) {
                return $response;
            }
        }

        // build the response
        $list_cards = $cardsRepo->findAll();

        $cards = array();
        /* @var CardInterface $card */
        foreach ($list_cards as $card) {
            $cards[] = $cardsData->getCardInfo($card, true, $version);
        }

        $content = json_encode($cards);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);
        return $response;
    }

    /**
     * Get the description of all the card from a pack, as an array of JSON objects.
     *
     * @Route(
     *     "/api/public/cards/{pack_code}.{_format}",
     *     name="api_cards_pack",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json"},
     *     options={"i18n" = false}
     * )
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the Cards from One Pack",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     * @param string $pack_code
     * @param Request $request
     * @param CardsData $cardsData
     * @param int $cacheExpiration
     * @return Response|NotFoundHttpException
     * @throws Exception
     */
    public function listCardsByPackAction($pack_code, Request $request, CardsData $cardsData, int $cacheExpiration)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        $pack = $this->getDoctrine()->getRepository(Pack::class)->findOneBy(array('code' => $pack_code));
        if (!$pack instanceof PackInterface) {
            throw $this->createNotFoundException();
        }

        $conditions = $cardsData->syntax("e:$pack_code");
        $cardsData->validateConditions($conditions);
        $query = $cardsData->buildQueryFromConditions($conditions);

        $cards = array();
        $last_modified = null;
        if ($query && $rows = $cardsData->getSearchRows($conditions, "set")) {
            for ($rowindex = 0; $rowindex < count($rows); $rowindex++) {
                if (empty($last_modified) || $last_modified < $rows[$rowindex]->getDateUpdate()) {
                    $last_modified = $rows[$rowindex]->getDateUpdate();
                }
            }
            $response->setLastModified($last_modified);
            if ($response->isNotModified($request)) {
                return $response;
            }
            for ($rowindex = 0; $rowindex < count($rows); $rowindex++) {
                $card = $cardsData->getCardInfo($rows[$rowindex], true, $version);
                $cards[] = $card;
            }
        }

        $content = json_encode($cards);
        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }
        $response->setContent($content);

        return $response;
    }

    /**
     * Get the description of a decklist as a JSON object.
     *
     * @Route(
     *     "/api/public/decklist/{decklist_id}.{_format}",
     *     name="api_decklist",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json", "decklist_id"="\d+"},
     *     options={"i18n" = false}
     * )
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="One Decklist",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
    *
     * @param string $decklist_id
     * @param Request $request
     * @param int $cacheExpiration
     * @return Response|NotFoundHttpException
     */
    public function getDecklistAction($decklist_id, Request $request, int $cacheExpiration)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        /* @var DecklistInterface $decklist */
        $decklist = $this->getDoctrine()->getRepository(Decklist::class)->find($decklist_id);
        if (!$decklist instanceof Decklist) {
            throw $this->createNotFoundException();
        }

        $response->setLastModified($decklist->getDateUpdate());
        if ($response->isNotModified($request)) {
            return $response;
        }

        $content = json_encode($decklist);

        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }

        $response->setContent($content);
        return $response;
    }

    /**
     * Get the description of all the decklists published at a given date, as an array of JSON objects.
     *
     * @Route(
     *     "/api/public/decklists/by_date/{date}.{_format}",
     *     name="api_decklists_by_date",
     *     methods={"GET"},
     *     defaults={"_format"="json"},
     *     requirements={"_format"="json", "decklist_id"="\d\d\d\d-\d\d-\d\d"},
     *     options={"i18n" = false}
     * )
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the Decklists from One Day",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     * @param string $date
     * @param Request $request
     * @param int $cacheExpiration
     * @return Response
     */
    public function listDecklistsByDateAction($date, Request $request, int $cacheExpiration)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        $start = DateTime::createFromFormat('Y-m-d', $date);
        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->add(new DateInterval("P1D"));

        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->gte('dateCreation', $start));
        $criteria->andWhere($expr->lt('dateCreation', $end));

        $decklists = $this->getDoctrine()->getRepository(Decklist::class)->matching($criteria);
        if (!$decklists) {
            die();
        }

        $dateUpdates = $decklists->map(function ($decklist) {
            return $decklist->getDateUpdate();
        })->toArray();

        if (! empty($dateUpdates)) {
            $response->setLastModified(max($dateUpdates));
        }
        if ($response->isNotModified($request)) {
            return $response;
        }

        $content = json_encode($decklists->toArray());

        if (isset($jsonp)) {
            $content = "$jsonp($content)";
            $response->headers->set('Content-Type', 'application/javascript');
        } else {
            $response->headers->set('Content-Type', 'application/json');
        }

        $response->setContent($content);
        return $response;
    }

    /**
     * @Route("/api/public/restrictions/", name="api_restrictions", methods={"GET"}, options={"i18n" = false})
     *
     * Get the description of all the restricted lists as an array of JSON objects.
     *
     * @Operation(
     *     tags={"Public"},
     *     summary="All the restricted lists.",
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     ),
     *     @SWG\Parameter(
     *         name="activeonly",
     *         in="query",
     *         description="Pass TRUE to retrieve active RLs only",
     *         required=false,
     *         type="string",
     *         @SWG\Schema(type="string")
     *     ),
     * )
     *
     * @param Request $request
     * @param int $cacheExpiration
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function listRestrictions(Request $request, int $cacheExpiration, SerializerInterface $serializer): Response
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $repo = $this->getDoctrine()->getRepository(Restriction::class);

        $filters = [];
        if ($request->query->getBoolean('activeonly')) {
            $filters['active'] = true;
        };
        $restrictions = $repo->findBy($filters);

        // check the last-modified-since header

        $lastModified = null;
        /* @var RestrictionInterface $restriction */
        foreach ($restrictions as $restriction) {
            if (!$lastModified || $lastModified < $restriction->getDateUpdate()) {
                $lastModified = $restriction->getDateUpdate();
            }
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        $json = json_encode($restrictions);
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent($json);
        return $response;
    }
}
