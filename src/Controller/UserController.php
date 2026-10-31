<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Card;
use App\Entity\Decklist;
use App\Entity\Faction;
use App\Entity\User;
use App\Entity\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @package App\Controller
 */
class UserController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }
    /**
     * @param int $cacheExpiration
     * @param int $user_id
     * @param string $user_name
     * @param int $page
     * @return Response
     * @todo Eliminate unused args from route and action. [ST 2020/08/09]
     */
    #[Route(path: '/user/profile/{user_id}/{user_name}/{page}', name: 'user_profile_public', methods: ['GET'], defaults: ['page' => 1], requirements: ['user_id' => '\d+'])]
    public function publicProfileAction(int $cacheExpiration, $user_id, $user_name, $page)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        /* @var $em EntityManager */
        $em = $this->managerRegistry->getManager();

        /* @var UserInterface $user */
        $user = $em->getRepository(User::class)->find($user_id);
        if (! $user) {
            throw new NotFoundHttpException("No such user.");
        }

        return $this->render('User/profile_public.html.twig', array(
                'user' => $user
        ));
    }

    /**
     * @return Response
     */
    #[Route(path: '/user/profile_edit', name: 'user_profile_edit', methods: ['GET'])]
    public function editProfileAction()
    {
        $user = $this->getUser();

        $factions = $this->managerRegistry->getRepository(Faction::class)->findAll();

        return $this->render('User/profile_edit.html.twig', array(
                'user' => $user,
                'factions' => $factions
        ));
    }

    /**
     *
     * @param Request $request
     * @param SessionInterface $session
     * @return RedirectResponse
     */
    #[Route(path: '/user/profile_save', name: 'user_profile_save', methods: ['POST'])]
    public function saveProfileAction(Request $request, SessionInterface $session)
    {
        /* @var UserInterface $user */
        $user = $this->getUser();
        $em = $this->managerRegistry->getManager();

        $username = filter_var($request->get('username'), FILTER_SANITIZE_STRING);
        if ($username !== $user->getUserIdentifier()) {
            $user_existing = $em->getRepository(User::class)->findOneBy(array('username' => $username));

            if ($user_existing) {
                $session->getFlashBag()->set('error', "Username $username is already taken.");
                return $this->redirectToRoute('user_profile_edit');
            }

            $user->setUsername($username);
        }

        $email = filter_var($request->get('email'), FILTER_SANITIZE_STRING);
        if ($email !== $user->getEmail()) {
            $user->setEmail($email);
        }

        $resume = filter_var($request->get('resume'), FILTER_SANITIZE_STRING);
        $faction_code = filter_var($request->get('user_faction_code'), FILTER_SANITIZE_STRING);
        $notifAuthor = $request->get('notif_author') ? true : false;
        $notifCommenter = $request->get('notif_commenter') ? true : false;
        $notifMention = $request->get('notif_mention') ? true : false;
        $shareDecks = $request->get('share_decks') ? true : false;

        $user->setColor($faction_code);
        $user->setResume($resume);
        $user->setIsNotifAuthor($notifAuthor);
        $user->setIsNotifCommenter($notifCommenter);
        $user->setIsNotifMention($notifMention);
        $user->setIsShareDecks($shareDecks);

        $this->managerRegistry->getManager()->flush();

        $session->getFlashBag()->set('notice', "Successfully saved your profile.");

        return $this->redirectToRoute('user_profile_edit');
    }

    /**
     * @param Request $request
     * @param RouterInterface $router
     * @return Response
     */
    #[Route(path: '/api/public/user/info', name: 'user_info', methods: ['GET'])]
    public function infoAction(Request $request, RouterInterface $router)
    {
        $jsonp = $request->query->get('jsonp');

        $decklist_id = $request->query->get('decklist_id');
        $card_id = $request->query->get('card_id');

        $content = null;

        if ($this->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var UserInterface $user */
            $user = $this->getUser();
            $user_id = $user->getId();

            $public_profile_url = $router->generate('user_profile_public', array(
                    'user_id' => $user_id,
                    'user_name' => urlencode($user->getUserIdentifier())
            ));
            $content = array(
                    'public_profile_url' => $public_profile_url,
                    'id' => $user_id,
                    'name' => $user->getUserIdentifier(),
                    'faction' => $user->getColor(),
                    'donation' => $user->getDonation(),
            );

            if (isset($decklist_id)) {
                /* @var EntityManager $em */
                $em = $this->managerRegistry->getManager();
                /* @var DecklistInterface $decklist */
                $decklist = $em->getRepository(Decklist::class)->find($decklist_id);

                if ($decklist) {
                    $decklist_id = $decklist->getId();

                    $dbh = $this->managerRegistry->getConnection();

                    $content['is_liked'] = (bool) $dbh->executeQuery("SELECT
                        count(*)
                        from decklist d
                        join vote v on v.decklist_id=d.id
                        where v.user_id=?
                        and d.id=?", array($user_id, $decklist_id))->fetchOne();

                    $content['is_favorite'] = (bool) $dbh->executeQuery("SELECT
                        count(*)
                        from decklist d
                        join favorite f on f.decklist_id=d.id
                        where f.user_id=?
                        and d.id=?", array($user_id, $decklist_id))->fetchOne();

                    $content['is_author'] = ($user_id == $decklist->getUser()->getId());

                    $content['can_delete'] = ($decklist->getNbcomments() == 0)
                        && ($decklist->getNbfavorites() == 0)
                        && ($decklist->getnbVotes() == 0);
                }
            }

            if (isset($card_id)) {
                /* @var $em EntityManager */
                $em = $this->managerRegistry->getManager();
                /* @var CardInterface $card */
                $card = $em->getRepository(Card::class)->find($card_id);

                if ($card) {
                    $reviews = $card->getReviews();
                    /* @var ReviewInterface $review */
                    foreach ($reviews as $review) {
                        if ($review->getUser()->getId() === $user->getId()) {
                            $content['review_id'] = $review->getId();
                            $content['review_text'] = $review->getTextMd();
                        }
                    }
                }
            }
        }
        $content = json_encode($content);

        $response = new Response();
        $response->setPrivate();
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
     *
     * @param UserManagerInterface $userManager
     * @param MailerInterface $mailer
     * @param SessionInterface $session
     * @param RouterInterface $router
     * @param string $username
     * @return RedirectResponse|Response
     */
    #[Route(path: '/user/remind/{username}', name: 'remind_email', methods: ['GET'])]
    public function remindAction(
        UserManagerInterface $userManager,
        MailerInterface $mailer,
        SessionInterface $session,
        RouterInterface $router,
        $username
    ) {
        $user = $userManager->findUserByUsername($username);
        if (!$user) {
            throw new NotFoundHttpException("Cannot find user from username [$username]");
        }
        if (!$user->getConfirmationToken()) {
            return $this->render('User/remind-no-token.html.twig');
        }

        $mailer->sendConfirmationEmailMessage($user);

        $session->set('fos_user_send_confirmation_email/email', $user->getEmail());

        $url = $router->generate('fos_user_registration_check_email');
        return $this->redirect($url);
    }
}
