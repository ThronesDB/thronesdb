<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\Review;
use App\Entity\Reviewcomment;
use App\Entity\ReviewInterface;
use App\Entity\User;
use App\Entity\UserInterface;
use App\Services\Texts;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @package App\Controller
 */
class ReviewController extends AbstractController
{
    /**
     * @Route("/review/post", name="card_review_post", methods={"POST"})
     * @param Request $request
     * @param Texts $texts
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function postAction(Request $request, Texts $texts)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var UserInterface $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        // a user cannot post more reviews than her reputation
        if (count($user->getReviews()) >= $user->getReputation()) {
            throw new Exception("Your reputation doesn't allow you to write more reviews.");
        }

        $card_id = filter_var($request->get('card_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var CardInterface $card */
        $card = $em->getRepository(Card::class)->find($card_id);
        if (!$card) {
            throw new Exception("This card does not exist.");
        }

        if ($card->getPack()->getWorkInProgress()) {
            throw $this->createAccessDeniedException(
                "This card is considered work-in-progress and cannot be reviewed."
            );
        }
        /*
          if(!$card->getPack()->getDateRelease())
          {
          throw new \Exception("You may not write a review for an unreleased card.");
          }
         */
        // checking the user didn't already write a review for that card
        $review = $em->getRepository(Review::class)->findOneBy(array('card' => $card, 'user' => $user));
        if ($review) {
            throw new Exception("You cannot write more than 1 review for a given card.");
        }

        $review_raw = trim($request->get('review'));

        $review_raw = preg_replace(
            '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)'
            . '(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*'
            . '(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu',
            '[$1]($0)',
            $review_raw
        );

        $review_html = $texts->markdown($review_raw);
        if (!$review_html) {
            throw new Exception("Your review is empty.");
        }

        $review = new Review();
        $review->setCard($card);
        $review->setUser($user);
        $review->setTextMd($review_raw);
        $review->setTextHtml($review_html);
        $review->setNbVotes(0);

        $em->persist($review);

        $em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route("/review/edit", name="card_review_edit", methods={"POST"})
     * @todo Clean this up. Response should always be JSON response (may require frontend changes). [ST 2020/07/25]
     * @param Request $request
     * @param Texts $texts
     * @return JsonResponse|Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function editAction(Request $request, Texts $texts)
    {

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var UserInterface $user */
        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException("You are not logged in.");
        }

        $review_id = filter_var($request->get('review_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var ReviewInterface $review */
        $review = $em->getRepository(Review::class)->find($review_id);
        if (!$review) {
            throw new BadRequestHttpException("Unable to find review.");
        }
        if ($review->getUser()->getId() !== $user->getId()) {
            throw new UnauthorizedHttpException("You cannot edit this review.");
        }

        $review_raw = trim($request->get('review'));

        $review_raw = preg_replace(
            '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)'
            . '(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*'
            . '(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu',
            '[$1]($0)',
            $review_raw
        );

        $review_html = $texts->markdown($review_raw);
        if (!$review_html) {
            return new Response('Your review is empty.');
        }

        $review->setTextMd($review_raw);
        $review->setTextHtml($review_html);

        $em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }

    /**
     * @Route("/review/like", name="card_review_like", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function likeAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var UserInterface $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        $review_id = filter_var($request->request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var ReviewInterface $review */
        $review = $em->getRepository(Review::class)->find($review_id);
        if (!$review) {
            throw new Exception("Unable to find review.");
        }

        // a user cannot vote on her own review
        if ($review->getUser()->getId() != $user->getId()) {
            // checking if the user didn't already vote on that review
            $query = $em->getRepository(Review::class)
                    ->createQueryBuilder('r')
                    ->innerJoin('r.votes', 'u')
                    ->where('r.id = :review_id')
                    ->andWhere('u.id = :user_id')
                    ->setParameter('review_id', $review_id)
                    ->setParameter('user_id', $user->getId())
                    ->getQuery();

            $result = $query->getResult();
            if (empty($result)) {
                $author = $review->getUser();
                $author->setReputation($author->getReputation() + 1);
                $user->addReviewVote($review);
                $review->setNbVotes($review->getnbVotes() + 1);
                $em->flush();
            }
        }
        return new JsonResponse([
            'success' => true,
            'nbVotes' => $review->getNbVotes()
        ]);
    }

    /**
     * @Route(
     *     "/reviews/{page}",
     *     name="card_reviews_list",
     *     methods={"GET"},
     *     defaults={"page"=1},
     *     requirements={"page"="\d+"}
     * )
     * @param Request $request
     * @param int $cacheExpiration
     * @param int $page
     * @return Response
     */
    public function listAction(Request $request, int $cacheExpiration, $page = 1)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        $pagetitle = "Card Reviews";

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT r FROM App\Entity\Review r JOIN r.card c JOIN c.pack p ORDER BY r.dateCreation DESC";
        $query = $em->createQuery($dql)->setFirstResult($start)->setMaxResults($limit);

        $paginator = new Paginator($query, false);
        $maxcount = count($paginator);

        $reviews = [];
        foreach ($paginator as $review) {
            $reviews[] = $review;
        }

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $params = $request->query->all();

        $pages = [];
        for ($page = 1; $page <= $nbpages; $page ++) {
            $pages[] = array(
                "numero" => $page,
                "url" => $this->generateUrl($route, $params + array(
                    "page" => $page
                )),
                "current" => $page == $currpage
            );
        }

        return $this->render('Reviews/reviews.html.twig', array(
                    'pagetitle' => $pagetitle,
                    'pagedescription' => "Read the latest user-submitted reviews on the cards.",
                    'reviews' => $reviews,
                    'url' => $request->getRequestUri(),
                    'route' => $route,
                    'pages' => $pages,
                    'prevurl' => $currpage == 1 ? null : $this->generateUrl($route, $params + array(
                        "page" => $prevpage
                    )),
                    'nexturl' => $currpage == $nbpages ? null : $this->generateUrl($route, $params + array(
                        "page" => $nextpage
                    ))
                        ), $response);
    }

    /**
     * @Route(
     *     "/user/reviews/{user_id}/{page}",
     *     name="card_reviews_list_byauthor",
     *     methods={"GET"},
     *     defaults={"page"=1},
     *     requirements={"page"="\d+", "user_id"="\d+"}
     * )
     * @param Request $request
     * @param int $cacheExpiration
     * @param int $user_id
     * @param int $page
     * @return Response
     */
    public function byauthorAction(Request $request, int $cacheExpiration, $user_id, $page = 1)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($cacheExpiration);

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository(User::class)->find($user_id);

        $pagetitle = "Card Reviews by " . $user->getUsername();

        $dql = "SELECT r FROM App\Entity\Review r WHERE r.user = :user ORDER BY r.dateCreation DESC";
        $query = $em->createQuery($dql)->setFirstResult($start)->setMaxResults($limit)->setParameter('user', $user);

        $paginator = new Paginator($query, false);
        $maxcount = count($paginator);

        $reviews = [];
        foreach ($paginator as $review) {
            $reviews[] = $review;
        }

        // pagination : calcul de nbpages // currpage // prevpage // nextpage
        // à partir de $start, $limit, $count, $maxcount, $page

        $currpage = $page;
        $prevpage = max(1, $currpage - 1);
        $nbpages = min(10, ceil($maxcount / $limit));
        $nextpage = min($nbpages, $currpage + 1);

        $route = $request->get('_route');

        $params = $request->query->all();

        $pages = [];
        for ($page = 1; $page <= $nbpages; $page ++) {
            $pages[] = array(
                "numero" => $page,
                "url" => $this->generateUrl($route, $params + array(
                    "user_id" => $user_id,
                    "page" => $page
                )),
                "current" => $page == $currpage
            );
        }

        return $this->render('Reviews/reviews.html.twig', array(
            'pagetitle' => $pagetitle,
            'pagedescription' => "Read the latest user-submitted reviews on the cards.",
            'reviews' => $reviews,
            'url' => $request->getRequestUri(),
            'route' => $route,
            'pages' => $pages,
            'prevurl' => $currpage == 1 ? null : $this->generateUrl($route, $params + array(
                "user_id" => $user_id,
                "page" => $prevpage
            )),
            'nexturl' => $currpage == $nbpages ? null : $this->generateUrl($route, $params + array(
                "user_id" => $user_id,
                "page" => $nextpage
            ))
        ), $response);
    }

    /**
     * @Route("/review/comment", name="card_reviewcomment_post", methods={"POST"})
     * @param Request $request
     * @param Swift_Mailer $mailer
     * @param string $emailSenderAddress
     * @return JsonResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function commentAction(Request $request, Swift_Mailer $mailer, string $emailSenderAddress)
    {
        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /* @var UserInterface $user */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        $review_id = filter_var($request->get('comment_review_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var ReviewInterface $review */
        $review = $em->getRepository(Review::class)->find($review_id);
        if (!$review) {
            throw new Exception("Unable to find review.");
        }

        $comment_text = trim($request->get('comment'));
        $comment_text = htmlspecialchars($comment_text);
        if (!$comment_text) {
            throw new Exception('Your comment is empty.');
        }

        $comment = new Reviewcomment();
        $comment->setReview($review);
        $comment->setUser($user);
        $comment->setText($comment_text);

        $em->persist($comment);

        $em->flush();

        // send emails
        $spool = [];
        if ($review->getUser()->getIsNotifAuthor()) {
            if (!isset($spool[$review->getUser()->getEmail()])) {
                $spool[$review->getUser()->getEmail()] = 'Emails/newreviewcomment_author.html.twig';
            }
        }
        unset($spool[$user->getEmail()]);

        $email_data = array(
            'username' => $user->getUsername(),
            'card_name' => $review->getCard()->getName(),
            'url' => $this->generateUrl(
                'cards_zoom',
                array('card_code' => $review->getCard()->getCode()),
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'comment' => $comment->getText(),
            'profile' => $this->generateUrl('user_profile_edit', [], UrlGeneratorInterface::ABSOLUTE_URL)
        );
        foreach ($spool as $email => $view) {
            $message = (new Swift_Message("[thronesdb] New review comment"))
                    ->setFrom(array($emailSenderAddress => $user->getUsername()))
                    ->setTo($email)
                    ->setBody($this->renderView($view, $email_data), 'text/html');
            $$mailer->send($message);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }
}
