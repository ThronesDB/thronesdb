<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\Deck;
use App\Entity\Deckchange;
use App\Entity\DeckInterface;
use App\Entity\Decklist;
use App\Entity\DecklistInterface;
use App\Entity\Deckslot;
use App\Entity\Faction;
use App\Entity\FactionInterface;
use App\Entity\Pack;
use App\Entity\Tournament;
use App\Entity\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Class BuilderController
 * @package App\Controller
 */
class BuilderController extends Controller
{
    /**
     * @const EXCLUDED_AGENDAS Codes of agendas that should not be available for selection in the new deck wizard.
     * @todo Hardwiring those is good enough for now, rethink this if/as this list grows [ST 2019/04/04]
     */
    const EXCLUDED_AGENDAS = [
        '00001', // The Power of Wealth (VDS)
        '00002', // Protectors of the Realm (VDS)
        '00003', // Treaty (VDS)
        '00004', // Uniting the Seven Kingdoms (VDS)
        "00030", // The King's Voice (VHotK)
    ];

    public function buildformAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));


        $em = $this->getDoctrine()->getManager();

        $factions = $em->getRepository(Faction::class)->findPrimaries();
        $agendas = $em->getRepository(Card::class)->getAgendasForNewDeckWizard(self::EXCLUDED_AGENDAS);

        return $this->render(
            'Builder/initbuild.html.twig',
            [
                'pagetitle' => $this->get('translator')->trans('decks.form.new'),
                'factions' => $factions,
                'agendas' => $agendas,
            ],
            $response
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function initbuildAction(Request $request)
    {
        $translator = $this->get('translator');

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $faction_code = $request->request->get('faction');
        $agenda_code = $request->request->get('agenda');

        if (!$faction_code) {
            $this->get('session')->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

            return $this->redirect($this->generateUrl('deck_buildform'));
        }

        $faction = $em->getRepository(Faction::class)->findByCode($faction_code);
        if (!$faction) {
            $this->get('session')->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

            return $this->redirect($this->generateUrl('deck_buildform'));
        }
        $tags = [$faction_code];

        if (!$agenda_code) {
            $agenda = null;
            $name = $translator->trans(
                "decks.build.newname.noagenda",
                array(
                    "%faction%" => $faction->getName(),
                )
            );
            $pack = $em->getRepository(Pack::class)->findOneBy(array("code" => "Core"));
        } else {
            $agenda = $em->getRepository(Card::class)->findByCode($agenda_code);
            $name = $translator->trans(
                "decks.build.newname.noagenda",
                array(
                    "%faction%" => $faction->getName(),
                    "%agenda%" => $agenda->getName(),
                )
            );
            $pack = $agenda->getPack();
            $tags[] = $this->get('agenda_helper')->getMinorFactionCode($agenda);
        }


        /** @var UserInterface $user */
        $user = $this->getUser();

        $deck = new Deck();
        $deck->setDescriptionMd("");
        $deck->setFaction($faction);
        $deck->setLastPack($pack);
        $deck->setName($name);
        $deck->setProblem('too_few_cards');
        $deck->setTags(join(' ', array_unique($tags)));
        $deck->setUser($user);
        $deck->setUuid(Uuid::uuid4());
        if ($agenda) {
            $slot = new Deckslot();
            $slot->setCard($agenda);
            $slot->setQuantity(1);
            $slot->setDeck($deck);
            $deck->addSlot($slot);
        }

        $em->persist($deck);
        $em->flush();

        return $this->redirect($this->get('router')->generate('deck_edit', ['deck_uuid' => $deck->getUuid()]));
    }

    public function importAction()
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $factions = $this->getDoctrine()->getRepository(Faction::class)->findAll();

        return $this->render(
            'Builder/directimport.html.twig',
            array(
                'pagetitle' => "Import a deck",
                'factions' => array_map(
                    function (FactionInterface $faction) {
                        return ['code' => $faction->getCode(), 'name' => $faction->getName()];
                    },
                    $factions
                ),
            ),
            $response
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function fileimportAction(Request $request)
    {
        $uploadedFile = $request->files->get('upfile');
        if (!isset($uploadedFile)) {
            throw new BadRequestHttpException("No file");
        }

        $origname = $uploadedFile->getClientOriginalName();
        $origext = $uploadedFile->getClientOriginalExtension();
        $filename = $uploadedFile->getPathname();
        $name = str_replace(".$origext", '', $origname);

        if (function_exists("finfo_open")) {
            // return mime type ala mimetype extension
            $finfo = finfo_open(FILEINFO_MIME);

            // check to see if the mime-type starts with 'text'
            $is_text = substr(finfo_file($finfo, $filename), 0, 4) == 'text'
                || substr(finfo_file($finfo, $filename), 0, 15) == "application/xml";
            if (!$is_text) {
                throw new BadRequestHttpException("Unsupported file format");
            }
        }

        $service = $this->get('deck_import_service');
        $data = $service->parseTextImport(file_get_contents($filename));

        if (empty($data['faction'])) {
            return $this->render(
                'Default/error.html.twig',
                [
                    'error' => "Unable to recognize the Faction of the deck.",
                ]
            );
        }

        $deck = new Deck();
        $deck->setUuid(Uuid::uuid4());

        $this->get('deck_manager')->save(
            $this->getUser(),
            $deck,
            null,
            $name,
            $data['faction'],
            $data['description'],
            null,
            $data['content'],
            null
        );

        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('decks_list'));
    }

    public function downloadAction(Request $request, $deck_uuid)
    {
        /* @var DeckInterface $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository(Deck::class)->findOneBy(['uuid' => $deck_uuid]);

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $format = $request->query->get('format', 'text');

        switch ($format) {
            case 'octgn':
                return $this->downloadInOctgnFormat($deck);
                break;
            case 'text_cycle':
                return $this->downloadInTextFormatSortedByCycle($deck);
                break;
            case 'text':
            default:
                return $this->downloadInDefaultTextFormat($deck);
        }
    }

    /**
     * @param DeckInterface $deck
     * @return Response
     */
    protected function downloadInOctgnFormat(DeckInterface $deck)
    {
        $content = $this->renderView(
            'Export/octgn.xml.twig',
            [
                "deck" => $deck->getTextExport(),
            ]
        );

        $response = new Response();
        $response->headers->set('Content-Type', 'application/octgn');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.o8d'
            )
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * @param DeckInterface $deck
     * @return Response
     */
    protected function downloadInDefaultTextFormat(DeckInterface $deck)
    {
        $content = $this->renderView(
            'Export/default.txt.twig',
            [
                "deck" => $deck->getTextExport(),
            ]
        );
        $content = str_replace("\n", "\r\n", $content);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * @param DeckInterface $deck
     * @return Response
     */
    protected function downloadInTextFormatSortedByCycle(DeckInterface $deck)
    {
        $content = $this->renderView(
            'Export/sortedbycycle.txt.twig',
            [
                "deck" => $deck->getCycleOrderExport(),
            ]
        );
        $content = str_replace("\n", "\r\n", $content);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $this->get('texts')->slugify($deck->getName()).'.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    public function cloneAction($deck_uuid)
    {
        /* @var DeckInterface $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository(Deck::class)->findOneBy(['uuid' => $deck_uuid]);

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $content = [];
        foreach ($deck->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }

        return $this->forward(
            'App\Controller\BuilderController:saveAction',
            array(
                'name' => $deck->getName().' (clone)',
                'faction_code' => $deck->getFaction()->getCode(),
                'content' => json_encode($content),
                'deck_id' => $deck->getParent() ? $deck->getParent()->getId() : null,
            )
        );
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveAction(Request $request)
    {

        /* @var EntityManager $em*/
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (count($user->getDecks()) > $user->getMaxNbDecks()) {
            return new Response(
                'You have reached the maximum number of decks allowed. Delete some decks or increase your reputation.'
            );
        }

        $id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        $deck = null;
        $source_deck = null;
        if ($id) {
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck || $user->getId() != $deck->getUser()->getId()) {
                throw new UnauthorizedHttpException("You don't have access to this deck.");
            }
            $source_deck = $deck;
        }

        $faction_code = filter_var($request->get('faction_code'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        if (!$faction_code) {
            return new Response('Cannot import deck without faction');
        }
        $faction = $em->getRepository(Faction::class)->findOneBy(['code' => $faction_code]);
        if (!$faction) {
            return new Response('Cannot import deck with unknown faction '.$faction_code);
        }

        $cancel_edits = (boolean)filter_var($request->get('cancel_edits'), FILTER_SANITIZE_NUMBER_INT);
        if ($cancel_edits) {
            if ($deck) {
                $this->get('deck_manager')->revert($deck);
            }

            return $this->redirect($this->generateUrl('decks_list'));
        }

        $is_copy = (boolean)filter_var($request->get('copy'), FILTER_SANITIZE_NUMBER_INT);
        if ($is_copy || !$id) {
            $deck = new Deck();
            $deck->setUuid(Uuid::uuid4());
        }

        $content = (array)json_decode($request->get('content'));
        if (!count($content)) {
            return new Response('Cannot import empty deck');
        }

        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
        $description = trim($request->get('description'));
        $tags = filter_var($request->get('tags'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        $this->get('deck_manager')->save(
            $this->getUser(),
            $deck,
            $decklist_id,
            $name,
            $faction,
            $description,
            $tags,
            $content,
            $source_deck ? $source_deck : null
        );
        $em->flush();

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $deck_id = filter_var($request->get('deck_id'), FILTER_SANITIZE_NUMBER_INT);
        /** @var DeckInterface $deck */
        $deck = $em->getRepository(Deck::class)->find($deck_id);
        if (!$deck) {
            return $this->redirect($this->generateUrl('decks_list'));
        }
        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            throw new UnauthorizedHttpException("You don't have access to this deck.");
        }

        foreach ($deck->getChildren() as $decklist) {
            /** @var DecklistInterface $decklist */
            $decklist->setParent(null);
        }
        $em->remove($deck);
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Deck deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteListAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $list_id = explode('-', $request->get('ids'));

        foreach ($list_id as $id) {
            /* @var DeckInterface $deck */
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }

            foreach ($deck->getChildren() as $decklist) {
                $decklist->setParent(null);
            }
            $em->remove($deck);
        }
        $em->flush();

        $this->get('session')
            ->getFlashBag()
            ->set('notice', "Decks deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    public function editAction($deck_uuid)
    {
        /** @var DeckInterface $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository(Deck::class)->findOneBy(['uuid' => $deck_uuid]);

        if ($this->getUser()->getId() != $deck->getUser()->getId()) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.',
                )
            );
        }

        return $this->render(
            'Builder/deckedit.html.twig',
            array(
                'pagetitle' => "Deckbuilder",
                'deck' => $deck,
            )
        );
    }

    /**
     * @param $deck_uuid
     * @return Response
     */
    public function viewAction($deck_uuid)
    {
        /** @var DeckInterface $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository(Deck::class)->findOneBy(['uuid' => $deck_uuid]);

        if (!$deck) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => "This deck doesn't exist.",
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $tournaments = $this->getDoctrine()->getManager()->getRepository(Tournament::class)->findAll();

        return $this->render(
            'Builder/deckview.html.twig',
            array(
                'pagetitle' => "Deckbuilder",
                'deck' => $deck,
                'is_owner' => $is_owner,
                'tournaments' => $tournaments,
            )
        );
    }

    /**
     * @param int $deck1_uuid
     * @param int $deck2_uuid
     * @return Response
     */
    public function compareAction($deck1_uuid, $deck2_uuid)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository(Deck::class);

        /* @var DeckInterface $deck1 */
        $deck1 = $repo->findOneBy(['uuid' => $deck1_uuid]);
        /* @var DeckInterface $deck2 */
        $deck2 = $repo->findOneBy(['uuid' => $deck2_uuid]);

        if (!$deck1 || !$deck2) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'This deck cannot be found.',
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck1->getUser()->getId();
        if (!$deck1->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck2->getUser()->getId();
        if (!$deck2->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        .' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $plotIntersection = $this->get('diff')->getSlotsDiff(
            [
                $deck1->getSlots()->getPlotDeck(),
                $deck2->getSlots()->getPlotDeck(),
            ]
        );

        $drawIntersection = $this->get('diff')->getSlotsDiff(
            [
                $deck1->getSlots()->getDrawDeck(),
                $deck2->getSlots()->getDrawDeck(),
            ]
        );

        return $this->render(
            'Compare/deck_compare.html.twig',
            [
                'deck1' => $deck1,
                'deck2' => $deck2,
                'plot_deck' => $plotIntersection,
                'draw_deck' => $drawIntersection,
            ]
        );
    }

    public function listAction()
    {
        /* @var UserInterface $user */
        $user = $this->getUser();

        $decks = $this->get('deck_manager')->getByUser($user);

        /* @todo refactor this out, use DQL not raw SQL [ST 2019/04/04] */
        $tournaments = $this->getDoctrine()->getConnection()->executeQuery(
            "SELECT t.id, t.description FROM tournament t ORDER BY t.description desc"
        )->fetchAll();

        if (count($decks)) {
            $tags = [];
            foreach ($decks as $deck) {
                /* @var DeckInterface $deck */
                $tags[] = $deck->getTags();
            }
            $tags = array_unique($tags);

            return $this->render(
                'Builder/decks.html.twig',
                array(
                    'pagetitle' => $this->get("translator")->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'decks' => $decks,
                    'tags' => $tags,
                    'nbmax' => $user->getMaxNbDecks(),
                    'nbdecks' => count($decks),
                    'cannotcreate' => $user->getMaxNbDecks() <= count($decks),
                    'tournaments' => $tournaments,
                )
            );
        } else {
            return $this->render(
                'Builder/no-decks.html.twig',
                array(
                    'pagetitle' => $this->get("translator")->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'nbmax' => $user->getMaxNbDecks(),
                    'tournaments' => $tournaments,
                )
            );
        }
    }

    public function copyAction($decklist_id)
    {
        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /* @var DecklistInterface $decklist */
        $decklist = $em->getRepository(Decklist::class)->find($decklist_id);

        $content = [];
        foreach ($decklist->getSlots() as $slot) {
            $content[$slot->getCard()->getCode()] = $slot->getQuantity();
        }

        return $this->forward(
            'App\Controller\BuilderController:saveAction',
            array(
                'name' => $decklist->getName(),
                'faction_code' => $decklist->getFaction()->getCode(),
                'content' => json_encode($content),
                'decklist_id' => $decklist_id,
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function autosaveAction(Request $request)
    {
        $user = $this->getUser();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $deck_id = $request->get('deck_id');

        /* @var DeckInterface $deck */
        $deck = $em->getRepository(Deck::class)->find($deck_id);
        if (!$deck) {
            throw new BadRequestHttpException("Cannot find deck ".$deck_id);
        }
        if ($user->getId() != $deck->getUser()->getId()) {
            throw new UnauthorizedHttpException("You don't have access to this deck.");
        }

        $diff = (array)json_decode($request->get('diff'));
        if (count($diff) != 2) {
            $this->get('logger')->error("cannot use diff", $diff);
            throw new BadRequestHttpException("Wrong content ".json_encode($diff));
        }

        if (count($diff[0]) || count($diff[1])) {
            $change = new Deckchange();
            $change->setDeck($deck);
            $change->setVariation(json_encode($diff));
            $change->setIsSaved(false);
            $em->persist($change);
            $em->flush();
        }

        return new Response($change->getDatecreation()->format('c'));
    }
}
