<?php

namespace App\Controller;

use App\Entity\DecklistInterface;
use App\Entity\Faction;
use App\Entity\Type;
use App\Entity\TypeInterface;
use App\Helper\AgendaHelper;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Model\DecklistManager;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    use LocaleAwareTemplating;

    /**
     * @Route("/", name="index", methods={"GET"})
     *
     * @return Response
     * @throws Exception
     */
    public function indexAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        // @todo inject service as method argument [ST 2020/08/01]
        $decklist_manager = $this->get(DecklistManager::class);
        $decklist_manager->setLimit(1);

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

            $decklist_manager->setFaction($faction);
            $paginator = $decklist_manager->findDecklistsByPopularity();
            /* @var DecklistInterface $decklist */
            $decklist = $paginator->getIterator()->current();

            if ($decklist) {
                $array['decklist'] = $decklist;

                $countByType = $decklist->getSlots()->getCountByType();
                $counts = [];
                foreach ($countByType as $code => $qty) {
                    $typeName = $typeNames[$code];
                    $counts[] = $qty . " " . $typeName . "s";
                }
                $array['count_by_type'] = join(' &bull; ', $counts);

                $factions = [ $faction->getName() ];
                foreach ($decklist->getSlots()->getAgendas() as $agenda) {
                    // @todo inject service as method argument [ST 2020/08/01]
                    $minor_faction = $this->get(AgendaHelper::class)->getMinorFaction($agenda->getCard());
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

        $game_name = $this->container->getParameter('game_name');
        $publisher_name = $this->container->getParameter('publisher_name');

        return $this->render('Default/index.html.twig', [
            'pagetitle' =>  "$game_name Deckbuilder",
            'pagedescription' => "Build your deck for $game_name by $publisher_name."
                . " Browse the cards and the thousand of decklists submitted by the community."
                . " Publish your own decks and get feedback.",
            'decklists_by_faction' => $decklists_by_faction
        ], $response);
    }

    /**
     * @Route("/rulesreference", name="rulesreference", methods={"GET"})
     * @return Response
     */
    public function rulesreferenceAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $page = $this->renderView(
            'Default/rulesreference.html.twig',
            array("pagetitle" => $this->get("translator")->trans("nav.rules"), "pagedescription" => "Rules Reference")
        );
        $response->setContent($page);
        return $response;
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     * @return Response
     */
    public function faqAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $page = $this->renderView(
            'Default/faq.html.twig',
            array("pagetitle" => $this->get("translator")->trans("nav.rules"), "pagedescription" => "F.A.Q")
        );
        $response->setContent($page);
        return $response;
    }

    /**
     * @Route("/tournamentregulations", name="tournamentregulations", methods={"GET"})
     * @return Response
     */
    public function tournamentregulationsAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $page = $this->renderView(
            'Default/tournamentregulations.html.twig',
            array(
                "pagetitle" => $this->get("translator")->trans("nav.rules"),
                "pagedescription" => "Tournament Regulations"
            )
        );
        $response->setContent($page);
        return $response;
    }

    /**
     * @Route("/about", name="about", methods={"GET"})
     * @param Request $request
     * @return Response
     */
    public function aboutAction(Request $request)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render($this->getLocaleSpecificViewPath('about', $request->getLocale()), array(
                "pagetitle" => "About",
                "game_name" => $this->container->getParameter('game_name'),
        ), $response);
    }

    /**
     * @Route("/api/", name="api_intro", methods={"GET"}, options={"i18n" = false})
     * @return Response
     */
    public function apiIntroAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        return $this->render('Default/apiIntro.html.twig', array(
                "pagetitle" => "API",
                "game_name" => $this->container->getParameter('game_name'),
                "publisher_name" => $this->container->getParameter('publisher_name'),
        ), $response);
    }
}
