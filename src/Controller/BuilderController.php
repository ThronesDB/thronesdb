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
use App\Entity\Restriction;
use App\Entity\Tournament;
use App\Entity\UserInterface;
use App\Services\AgendaHelper;
use App\Services\DeckImportService;
use App\Services\DeckManager;
use App\Services\Diff;
use App\Services\RestrictionsChecker;
use App\Services\Texts;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @package App\Controller
 */
class BuilderController extends AbstractController
{
    /**
     * @const EXCLUDED_AGENDAS Codes of agendas that should not be available for selection in the new deck wizard.
     * @todo Hardwiring those is good enough for now, rethink this if/as this list grows [ST 2019/04/04]
     */
    protected const EXCLUDED_AGENDAS = [
        '00001', // The Power of Wealth (VDS)
        '00002', // Protectors of the Realm (VDS)
        '00003', // Treaty (VDS)
        '00004', // Uniting the Seven Kingdoms (VDS)
        "00030", // The King's Voice (VHotK)
    ];

    /**
     * @Route("/deck/new", name="deck_buildform", methods={"GET"})
     * @param int $cacheExpiration
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function buildformAction(int $cacheExpiration, TranslatorInterface $translator)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);


        $em = $this->getDoctrine()->getManager();

        $factions = $em->getRepository(Faction::class)->findPrimaries();
        $agendas = $em->getRepository(Card::class)->getAgendasForNewDeckWizard(self::EXCLUDED_AGENDAS);

        return $this->render(
            'Builder/initbuild.html.twig',
            [
                'pagetitle' => $translator->trans('decks.form.new'),
                'factions' => $factions,
                'agendas' => $agendas,
            ],
            $response
        );
    }

    /**
     * @Route("/deck/build", name="deck_initbuild", methods={"POST"})
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param SessionInterface $session
     * @param RouterInterface $router
     * @param AgendaHelper $agendaHelper
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function initbuildAction(
        Request $request,
        TranslatorInterface $translator,
        SessionInterface $session,
        RouterInterface $router,
        AgendaHelper $agendaHelper
    ) {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $faction_code = $request->request->get('faction');
        $agenda_code = $request->request->get('agenda');

        if (!$faction_code) {
            $session->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

            return $this->redirect($this->generateUrl('deck_buildform'));
        }

        $faction = $em->getRepository(Faction::class)->findByCode($faction_code);
        if (!$faction) {
            $session->getFlashBag()->set('error', $translator->trans("decks.build.errors.nofaction"));

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
            $tags[] = $agendaHelper->agendaToTag($agenda);
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

        return $this->redirect($router->generate('deck_edit', ['deck_uuid' => $deck->getUuid()]));
    }

    /**
     * @Route("/deck/import", name="deck_import", methods={"GET"})
     * @param int $cacheExpiration
     * @return Response
     */
    public function importAction(int $cacheExpiration)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

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
     * @Route("/deck/fileimport", name="deck_fileimport", methods={"POST"})
     * @param Request $request
     * @param DeckImportService $deckImportService
     * @param DeckManager $deckManager
     * @param SessionInterface $session
     * @param TranslatorInterface $translator
     * @throws BadRequestHttpException
     * @return RedirectResponse
     */
    public function fileimportAction(
        Request $request,
        DeckImportService $deckImportService,
        DeckManager $deckManager,
        SessionInterface $session,
        TranslatorInterface $translator
    ) {
        $uploadedFile = $request->files->get('upfile');
        if (!isset($uploadedFile)) {
            throw new BadRequestHttpException("No file");
        }

        $filename = $uploadedFile->getPathname();

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

        $parsedData = $deckImportService->parseTextImport(file_get_contents($filename));

        // Cancel import if number of given lists exceeds the number of available deck slots.
        // No partial import of (bulk) uploads is supported.
        /** @var UserInterface $user */
        $user = $this->getUser();
        $existingDecks = $deckManager->getByUser($user);
        $numberSuccessfullyParsedDecks = count($parsedData['decks']);
        $numberOfFailedParsedDecks = count($parsedData['errors']);
        $errorMessages = array_unique($parsedData['errors']);
        $numberOfDecksUploaded = $numberSuccessfullyParsedDecks + $numberOfFailedParsedDecks;

        if ($user->getMaxNbDecks() < $numberOfDecksUploaded + count($existingDecks)) {
            $session->getFlashBag()->set(
                'error',
                $translator->trans('decks.import.error.general')
                . ' ' .
                $translator->trans('decks.save.outOfSlots')
            );
            return $this->redirect($this->generateUrl('decks_list'));
        }

        // finally, import all the "good" decks(s)
        foreach ($parsedData['decks'] as $data) {
            $deck = new Deck();
            $deck->setUuid(Uuid::uuid4());

            $deckManager->save(
                $this->getUser(),
                $deck,
                null,
                $data['name'],
                $data['faction'],
                $data['description'],
                null,
                $data['content'],
                null
            );
        }

        $this->getDoctrine()->getManager()->flush();
        if ($numberSuccessfullyParsedDecks) {
            $session->getFlashBag()->set(
                'notice',
                $translator->transChoice(
                    "decks.import.success",
                    $numberOfDecksUploaded,
                    [ '%success%' => $numberSuccessfullyParsedDecks, '%all%' => $numberOfDecksUploaded ]
                )
            );
        }
        if ($numberOfFailedParsedDecks) {
            $session->getFlashBag()->set(
                'error',
                $translator->transChoice(
                    "decks.import.failures",
                    $numberOfDecksUploaded,
                    [ '%failures%' => $numberOfFailedParsedDecks, '%all%' => $numberOfDecksUploaded ]
                ) . " " .
                $translator->transChoice(
                    "decks.import.failureReasons",
                    count($errorMessages),
                    [ '%reasons%' => implode('", "', $errorMessages) ]
                )
            );
        }

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @Route(
     *     "/deck/export/octgn/{deck_uuid}",
     *     name="deck_download",
     *     methods={"GET"},
     *     requirements={"deck_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}"}
     * )
     * @param Request $request
     * @param Texts $texts
     * @param string $deck_uuid
     * @return Response
     */
    public function downloadAction(Request $request, Texts $texts, $deck_uuid)
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
                        . ' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $format = $request->query->get('format', 'text');

        switch ($format) {
            case 'octgn':
                return $this->downloadInOctgnFormat($deck, $texts);
                break;
            case 'text_cycle':
                return $this->downloadInTextFormatSortedByCycle($deck, $texts);
                break;
            case 'text':
            default:
                return $this->downloadInDefaultTextFormat($deck, $texts);
        }
    }

    /**
     * @Route(
     *     "/deck/clone/{deck_uuid}",
     *     name="deck_clone",
     *     methods={"GET"},
     *     requirements={"deck_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}"}
     * )
     * @param string $deck_uuid
     * @return Response
     */
    public function cloneAction($deck_uuid)
    {
        /** @var DeckInterface $deck */
        $deck = $this->getDoctrine()->getManager()->getRepository(Deck::class)->findOneBy(['uuid' => $deck_uuid]);

        $is_owner = $this->getUser() && $this->getUser()->getId() == $deck->getUser()->getId();
        if (!$deck->getUser()->getIsShareDecks() && !$is_owner) {
            return $this->render(
                'Default/error.html.twig',
                array(
                    'pagetitle' => "Error",
                    'error' => 'You are not allowed to view this deck.'
                        . ' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
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
                'name' => $deck->getName() . ' (clone)',
                'faction_code' => $deck->getFaction()->getCode(),
                'content' => json_encode($content),
                'parent_deck_id' => $deck->getId(),
            )
        );
    }

    /**
     * @Route("/deck/save", name="deck_save", methods={"POST"})
     * @param Request $request
     * @param DeckManager $deckManager
     * @param TranslatorInterface $translator
     * @return RedirectResponse|Response
     */
    public function saveAction(Request $request, DeckManager $deckManager, TranslatorInterface $translator)
    {

        /* @var EntityManager $em*/
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (count($user->getDecks()) > $user->getMaxNbDecks()) {
            return new Response(
                $translator->trans('decks.save.outOfSlots')
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
            return new Response('Cannot import deck with unknown faction ' . $faction_code);
        }

        $cancel_edits = (bool)filter_var($request->get('cancel_edits'), FILTER_SANITIZE_NUMBER_INT);
        if ($cancel_edits) {
            if ($deck) {
                $deckManager->revert($deck);
            }

            return $this->redirect($this->generateUrl('decks_list'));
        }

        $is_copy = (bool)filter_var($request->get('copy'), FILTER_SANITIZE_NUMBER_INT);

        if ($is_copy || !$id) {
            $deck = new Deck();
            $deck->setUuid(Uuid::uuid4());
            $parent_deck_id = filter_var($request->get('parent_deck_id'), FILTER_SANITIZE_NUMBER_INT);

            if ($parent_deck_id) {
                /* @var DeckInterface $parentDeck */
                $parentDeck = $em->getRepository(Deck::class)->find($parent_deck_id);
                if (!$parentDeck) {
                    throw new UnauthorizedHttpException("Parent deck not found.");
                }
                $deck->setParentDeck($parentDeck);
            }
        }

        $content = (array)json_decode($request->get('content'));
        if (!count($content)) {
            return new Response('Cannot import empty deck');
        }

        $name = filter_var($request->get('name'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        $decklist_id = filter_var($request->get('decklist_id'), FILTER_SANITIZE_NUMBER_INT);
        $description = trim($request->get('description'));
        $tags = filter_var($request->get('tags'), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

        $deckManager->save(
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
     * @Route("/deck/delete", name="deck_delete", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteAction(Request $request, SessionInterface $session)
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

        $session->getFlashBag()->set('notice', "Deck deleted.");

        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @Route("/deck/download_list", name="deck_download_list", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return Response
     */
    public function downloadListAction(Request $request, SessionInterface $session)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $ids = explode('-', $request->get('ids'));
        $decks = $em->getRepository(Deck::class)->findBy(['id' => $ids]);

        $currentUserId = $this->getUser()->getId();
        $decks = array_values(array_filter($decks, function (DeckInterface $deck) use ($currentUserId) {
            return $currentUserId === $deck->getUser()->getId();
        }));

        $exports = [];
        foreach ($decks as $deck) {
            $content = $this->renderView('Export/default.txt.twig', [ "deck" => $deck->getTextExport() ]);
            $exports[] = str_replace("\n", "\r\n", $content);
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');
        $response->headers->set(
            'Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'decks.txt'
            )
        );

        $response->setContent(implode("\r\n===\r\n", $exports));

        return $response;
    }

    /**
     * @Route("/deck/delete_list", name="deck_delete_list", methods={"POST"})
     * @param Request $request
     * @param SessionInterface $session
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteListAction(Request $request, SessionInterface $session)
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

        $session->getFlashBag()->set('notice', "Decks deleted.");
        return $this->redirect($this->generateUrl('decks_list'));
    }

    /**
     * @Route(
     *     "/deck/edit/{deck_uuid}",
     *     name="deck_edit",
     *     methods={"GET"},
     *     requirements={"deck_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}"}
     * )
     *
     * @param string $deck_uuid
     * @return Response
     */
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
     * @Route(
     *     "/deck/view/{deck_uuid}",
     *     name="deck_view",
     *     methods={"GET"},
     *     requirements={"deck_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}"}
     * )
     * @param string $deck_uuid
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
                        . ' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
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
     * @Route(
     *     "/deck/compare/{deck1_uuid}/{deck2_uuid}",
     *     name="decks_diff",
     *     methods={"GET"},
     *     requirements={
     *          "deck1_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}",
     *          "deck2_uuid"="[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}"
     *     }
     * )
     * @param string $deck1_uuid
     * @param string $deck2_uuid
     * @param Diff $diff
     * @return Response
     */
    public function compareAction($deck1_uuid, $deck2_uuid, Diff $diff)
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
                        . ' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
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
                        . ' To get access, you can ask the deck owner to enable "Share your decks" on their account.',
                )
            );
        }

        $plotIntersection = $diff->getSlotsDiff([$deck1->getSlots()->getPlotDeck(), $deck2->getSlots()->getPlotDeck()]);
        $drawIntersection = $diff->getSlotsDiff([$deck1->getSlots()->getDrawDeck(), $deck2->getSlots()->getDrawDeck()]);

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

    /**
     * @Route("/decks", name="decks_list", methods={"GET"})
     * @param DeckManager $deckManager
     * @param RestrictionsChecker $restrictionsChecker
     * @param TranslatorInterface $translator
     * @return Response
     */
    public function listAction(
        DeckManager $deckManager,
        RestrictionsChecker $restrictionsChecker,
        TranslatorInterface $translator
    ) {
        /* @var UserInterface $user */
        $user = $this->getUser();

        $decks = $deckManager->getByUser($user);

        /* @todo refactor this out, use DQL not raw SQL [ST 2019/04/04] */
        $tournaments = $this->getDoctrine()->getConnection()->executeQuery(
            "SELECT t.id, t.description FROM tournament t ORDER BY t.description desc"
        )->fetchAll();


        if (count($decks)) {
            // collect all deck tags
            $tags = [];
            foreach ($decks as $deck) {
                /* @var DeckInterface $deck */
                $tags[] = $deck->getTags();
            }
            $tags = array_unique($tags);

            // check all decks against all active RLs
            $restrictionsRepo = $this->getDoctrine()->getRepository(Restriction::class);
            $activeRestrictions = $restrictionsRepo->findBy(['active' => true], ['effectiveOn' => 'DESC']);

            $deckLegalityMap = [];

            foreach ($activeRestrictions as $restriction) {
                foreach ($decks as $deck) {
                    $deckId = $deck->getId();
                    if (! array_key_exists($deckId, $deckLegalityMap)) {
                        $deckLegalityMap[$deckId] = [];
                    }
                    $restrictionCode = $restriction->getCode();
                    $restrictionTitle = $restriction->getTitle();
                    if (! array_key_exists($restrictionCode, $deckLegalityMap[$deckId])) {
                        $deckLegalityMap[$deckId][$restrictionCode] = [
                            'title' => $restrictionTitle,
                            'joust' => $restrictionsChecker->isLegalForJoust($restriction, $deck),
                            'melee' => $restrictionsChecker->isLegalForMelee($restriction, $deck),
                        ];
                    }
                }
            }

            return $this->render(
                'Builder/decks.html.twig',
                array(
                    'pagetitle' => $translator->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'decks' => $decks,
                    'tags' => $tags,
                    'nbmax' => $user->getMaxNbDecks(),
                    'nbdecks' => count($decks),
                    'cannotcreate' => $user->getMaxNbDecks() <= count($decks),
                    'tournaments' => $tournaments,
                    'decklegality' => $deckLegalityMap,
                )
            );
        } else {
            return $this->render(
                'Builder/no-decks.html.twig',
                array(
                    'pagetitle' => $translator->trans('nav.mydecks'),
                    'pagedescription' => "Create custom decks with the help of a powerful deckbuilder.",
                    'nbmax' => $user->getMaxNbDecks(),
                    'tournaments' => $tournaments,
                )
            );
        }
    }

    /**
     * @Route("/deck/copy/{decklist_id}", name="deck_copy", methods={"GET"}, requirements={"decklist_id"="\d+"})
     * @param int $decklist_id
     * @return Response
     */
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
     * @Route("/deck/autosave", name="deck_autosave", methods={"POST"})
     * @param Request $request
     * @param LoggerInterface $logger
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function autosaveAction(Request $request, LoggerInterface $logger)
    {
        $user = $this->getUser();

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $deck_id = $request->get('deck_id');

        /* @var DeckInterface $deck */
        $deck = $em->getRepository(Deck::class)->find($deck_id);
        if (!$deck) {
            throw new BadRequestHttpException("Cannot find deck " . $deck_id);
        }
        if ($user->getId() != $deck->getUser()->getId()) {
            throw new UnauthorizedHttpException("You don't have access to this deck.");
        }

        $diff = (array)json_decode($request->get('diff'));
        if (count($diff) != 2) {
            $logger->error("cannot use diff", $diff);
            throw new BadRequestHttpException("Wrong content " . json_encode($diff));
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


    /**
     * @param DeckInterface $deck
     * @param Texts $texts
     * @return Response
     */
    protected function downloadInOctgnFormat(DeckInterface $deck, Texts $texts)
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
                $texts->slugify($deck->getName()) . '.o8d'
            )
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * @param DeckInterface $deck
     * @param Texts $texts
     * @return Response
     */
    protected function downloadInDefaultTextFormat(DeckInterface $deck, Texts $texts)
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
                $texts->slugify($deck->getName()) . '.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * @param DeckInterface $deck
     * @param Texts $texts
     * @return Response
     */
    protected function downloadInTextFormatSortedByCycle(DeckInterface $deck, Texts $texts)
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
                $texts->slugify($deck->getName()) . '.txt'
            )
        );

        $response->setContent($content);

        return $response;
    }

    /**
     * @Route("/decks/bulkclone", name="decks_bulkclone", methods={"POST", "GET"})
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param DeckManager $deckManager
     * @return Response|RedirectResponse
     * @throws Exception
     */
    public function bulkCloneDeckAction(
        Request $request,
        TranslatorInterface $translator,
        DeckManager $deckManager
    ): Response {

        /* @var EntityManager $em*/
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder([])
            ->add('decks', TextareaType::class, [
                'help' => "Put your list of deck URLs in here. One deck per line. <br>"
                    . "You may also provide a new deck name, which must be separated from the URL by <code>|</code>"
                    . ".<br><br>Example: " .
                    "<code>https://thronesdb.com/deck/view/XXXXXXXXX-XXXX-XXXX-XXXX-000000000001|New name</code>",
                'help_html' => true,
                'label' => 'Deck URLs',
                'attr' => ['rows' => 15],
            ])
            ->add('send', SubmitType::class, [
                'attr' => ['class' => 'btn-primary'],
                'label' => 'Clone Decks',
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (empty($data) || empty($data['decks'])) {
                $this->addFlash('error', 'Empty input. Please provide at least one deck URL.');
                return $this->render('Builder/bulkclone.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $lines = array_map('trim', explode(PHP_EOL, $data['decks']));
            $lines = array_values(array_filter($lines));

            if (empty($lines)) {
                $this->addFlash('error', 'Empty input. Please provide at least one deck URL.');
                return $this->render('Builder/bulkclone.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $totalDecksToClone = count($lines);

            /* @var UserInterface $user */
            $user = $this->getUser();
            $availableDeckSlots = $user->getMaxNbDecks();
            $numDecks = count($user->getDecks()) + $totalDecksToClone;
            if ($numDecks > $availableDeckSlots) {
                $this->addFlash('error', $translator->trans('decks.save.outOfSlots'));
                return $this->render('Builder/bulkclone.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $errors = [];
            $successes = [];
            $reUuid4 = '/[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}/';

            $deckRepo = $em->getRepository(Deck::class);

            $i = 0;
            foreach ($lines as $line) {
                $i++;
                $clean = [];
                $items = explode('|', $line, 2);
                $hasName = (2 === count($items));
                $clean['url'] = filter_var($items['0'], FILTER_SANITIZE_URL);
                $clean['name'] = $hasName
                    ? filter_var($items[1], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES)
                    : '';

                if (!preg_match($reUuid4, $clean['url'], $matches)) {
                    $errors[] = "[line ${i}] ${clean['url']}"
                        . ($hasName ? " (${clean['name']})" : '')
                        . ' is no a valid deck URL.';
                    continue;
                }
                /* @var DeckInterface $deck */
                $deck = $deckRepo->findOneBy(['uuid' => $matches[0]]);
                if (!$deck) {
                    $errors[] = "[line ${i}] Cannot find deck at ${clean['url']}"
                    . ($hasName ? " (${clean['name']})" : '') . '.';
                    continue;
                }
                $owner = $deck->getUser();
                if (!$owner->getIsShareDecks() && $owner->getId() !== $user->getId()) {
                    $errors[] = "[line ${i}] ${clean['url']}"
                        . ($hasName ? " (${clean['name']})" : '')
                        . ' cannot be accessed because deck-sharing is not enabled by its owner.';
                    continue;
                }

                $content = [];
                foreach ($deck->getSlots() as $slot) {
                    $content[$slot->getCard()->getCode()] = $slot->getQuantity();
                }
                $newDeck = new Deck();
                $newDeck->setUuid(Uuid::uuid4());
                $newDeck->setParentDeck($deck);

                $deckManager->save(
                    $user,
                    $newDeck,
                    null,
                    $hasName ? $clean['name'] : $deck->getName() . ' (Clone)',
                    $deck->getFaction(),
                    '',
                    $deck->getTags(),
                    $content,
                    null,
                );

                $newDeckUrl = $this->generateUrl(
                    'deck_view',
                    ['deck_uuid' => $newDeck->getUuid()->toString()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $successes[] = "<a href=\"${clean['url']}\" target=\"_blank\">${clean['url']}</a> "
                    . 'has been successfully cloned to '
                    . "<a href=\"${newDeckUrl}\" target=\"_blank\">${newDeckUrl}"
                    . ($hasName ? " (${clean['name']})" : '')
                    . "</a>.";
            }

            $em->flush();

            foreach ($errors as $error) {
                $this->addFlash('error', $error);
            }

            foreach ($successes as $success) {
                $this->addFlash('notice', $success);
            }

            return $this->redirect($this->generateUrl('decks_bulkclone'));
        }

        return $this->render('Builder/bulkclone.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
