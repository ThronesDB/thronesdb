<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Pack;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Decklist;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\Criteria;
use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApiController extends Controller
{

    /**
     * Get the description of all the packs as an array of JSON objects.
     *
     *
     * @Operation(
     *     tags={"Pack"},
     *     summary="All the Packs",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function listPacksAction(Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $jsonp = $request->query->get('jsonp');

        $list_packs = $this->getDoctrine()->getRepository('AppBundle:Pack')->findAll();

        // check the last-modified-since header

        $lastModified = null;
        /* @var $pack \AppBundle\Entity\Pack */
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
        /* @var $pack \AppBundle\Entity\Pack */
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
                "url" => $this->get('router')->generate(
                    'cards_list',
                    array('pack_code' => $pack->getCode()),
                    UrlGeneratorInterface::ABSOLUTE_URL
                ),
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
     * Get the description of a card as a JSON object.
     *
     * @Operation(
     *     tags={"Card"},
     *     summary="One Card",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function getCardAction($card_code, Request $request)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*',
            'Content-Language' => $request->getLocale()
        ));

        $jsonp = $request->query->get('jsonp');

        $card = $this->getDoctrine()->getRepository('AppBundle:Card')->findOneBy(array("code" => $card_code));
        if (!$card instanceof Card) {
            return $this->createNotFoundException();
        }

        // check the last-modified-since header

        $lastModified = null;
        /* @var $card \AppBundle\Entity\Card */
        if (!$lastModified || $lastModified < $card->getDateUpdate()) {
            $lastModified = $card->getDateUpdate();
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // build the response

        /* @var $card \AppBundle\Entity\Card */
        $card = $this->get('cards_data')->getCardInfo($card, true, $version);

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
     * @Operation(
     *     tags={"Card"},
     *     summary="All the Cards",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function listCardsAction(Request $request)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array(
            'Access-Control-Allow-Origin' => '*'
        ));

        $jsonp = $request->query->get('jsonp');

        $list_cards = $this->getDoctrine()->getRepository('AppBundle:Card')->findAll();

        // check the last-modified-since header

        $lastModified = null;
        /* @var $card \AppBundle\Entity\Card */
        foreach ($list_cards as $card) {
            if (!$lastModified || $lastModified < $card->getDateUpdate()) {
                $lastModified = $card->getDateUpdate();
            }
        }
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        // build the response

        $cards = array();
        /* @var $card \AppBundle\Entity\Card */
        foreach ($list_cards as $card) {
            $cards[] = $this->get('cards_data')->getCardInfo($card, true, $version);
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
     * @Operation(
     *     tags={"Card"},
     *     summary="All the Cards from One Pack",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function listCardsByPackAction($pack_code, Request $request)
    {
        $version = $request->query->get('v', '1.0');

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        $pack = $this->getDoctrine()->getRepository('AppBundle:Pack')->findOneBy(array('code' => $pack_code));
        if (!$pack instanceof Pack) {
            return $this->createNotFoundException();
        }

        $conditions = $this->get('cards_data')->syntax("e:$pack_code");
        $this->get('cards_data')->validateConditions($conditions);
        $query = $this->get('cards_data')->buildQueryFromConditions($conditions);

        $cards = array();
        $last_modified = null;
        if ($query && $rows = $this->get('cards_data')->getSearchRows($conditions, "set")) {
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
                $card = $this->get('cards_data')->getCardInfo($rows[$rowindex], true, $version);
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
     * @Operation(
     *     tags={"Decklist"},
     *     summary="One Decklist",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function getDecklistAction($decklist_id, Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        /* @var $decklist \AppBundle\Entity\Decklist */
        $decklist = $this->getDoctrine()->getRepository('AppBundle:Decklist')->find($decklist_id);
        if (!$decklist instanceof Decklist) {
            return $this->createNotFoundException();
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
     * @Operation(
     *     tags={"Decklist"},
     *     summary="All the Decklists from One Day",
     *     @SWG\Parameter(
     *         name="jsonp",
     *         in="query",
     *         description="JSONP callback",
     *         required=false,
     *         @SWG\Schema(type="string")
     *     ),
     *     @SWG\Response(
     *         response="200",
     *         description="Returned when successful"
     *     )
     * )
     *
     *
     * @param Request $request
     */
    public function listDecklistsByDateAction($date, Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));
        $response->headers->add(array('Access-Control-Allow-Origin' => '*'));

        $jsonp = $request->query->get('jsonp');

        $format = $request->getRequestFormat();
        if ($format !== 'json') {
            $response->setContent($request->getRequestFormat() . ' format not supported. Only json is supported.');
            return $response;
        }

        $start = \DateTime::createFromFormat('Y-m-d', $date);
        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->add(new \DateInterval("P1D"));

        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where($expr->gte('dateCreation', $start));
        $criteria->andWhere($expr->lt('dateCreation', $end));

        /* @var $decklists \Doctrine\Common\Collections\ArrayCollection */
        $decklists = $this->getDoctrine()->getRepository('AppBundle:Decklist')->matching($criteria);
        if (!$decklists) {
            die();
        }

        $dateUpdates = $decklists->map(function ($decklist) {
            return $decklist->getDateUpdate();
        })->toArray();

        $response->setLastModified(max($dateUpdates));
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
}
