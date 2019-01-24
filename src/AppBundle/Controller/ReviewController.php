<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Card;
use AppBundle\Entity\Review;
use AppBundle\Entity\Reviewcomment;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReviewController extends Controller
{
    public function postAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $user User */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        // a user cannot post more reviews than her reputation
        if (count($user->getReviews()) >= $user->getReputation()) {
            throw new \Exception("Your reputation doesn't allow you to write more reviews.");
        }

        $card_id = filter_var($request->get('card_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $card Card */
        $card = $em->getRepository('AppBundle:Card')->find($card_id);
        if (!$card) {
            throw new \Exception("This card does not exist.");
        }
        /*
          if(!$card->getPack()->getDateRelease())
          {
          throw new \Exception("You may not write a review for an unreleased card.");
          }
         */
        // checking the user didn't already write a review for that card
        $review = $em->getRepository('AppBundle:Review')->findOneBy(array('card' => $card, 'user' => $user));
        if ($review) {
            throw new \Exception("You cannot write more than 1 review for a given card.");
        }

        $review_raw = trim($request->get('review'));

        $review_raw = preg_replace(
            '%(?<!\()\b(?:(?:https?|ftp)://)(?:((?:(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)'
            . '(?:\.(?:[a-z\d\x{00a1}-\x{ffff}]+-?)*[a-z\d\x{00a1}-\x{ffff}]+)*'
            . '(?:\.[a-z\x{00a1}-\x{ffff}]{2,6}))(?::\d+)?)(?:[^\s]*)?%iu',
            '[$1]($0)',
            $review_raw
        );

        $review_html = $this->get('texts')->markdown($review_raw);
        if (!$review_html) {
            throw new \Exception("Your review is empty.");
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

    public function editAction(Request $request)
    {

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /* @var $user User */
        $user = $this->getUser();
        if (!$user) {
            throw new UnauthorizedHttpException("You are not logged in.");
        }

        $review_id = filter_var($request->get('review_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $review Review */
        $review = $em->getRepository('AppBundle:Review')->find($review_id);
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

        $review_html = $this->get('texts')->markdown($review_raw);
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

    public function likeAction(Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        $review_id = filter_var($request->request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $review Review */
        $review = $em->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw new \Exception("Unable to find review.");
        }

        // a user cannot vote on her own review
        if ($review->getUser()->getId() != $user->getId()) {
            // checking if the user didn't already vote on that review
            $query = $em->getRepository('AppBundle:Review')
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

    public function removeAction($id, Request $request)
    {
        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        if (!$user || !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('No user or not admin');
        }

        $review_id = filter_var($request->get('id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $review Review */
        $review = $em->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw new \Exception("Unable to find review.");
        }

        $votes = $review->getVotes();
        foreach ($votes as $vote) {
            $review->removeVote($vote);
        }
        $em->remove($review);
        $em->flush();

        return new JsonResponse([
            'success' => true
        ]);
    }

    public function listAction(Request $request, $page = 1)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        $pagetitle = "Card Reviews";

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $dql = "SELECT r FROM AppBundle:Review r JOIN r.card c JOIN c.pack p ORDER BY r.dateCreation DESC";
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

        return $this->render('AppBundle:Reviews:reviews.html.twig', array(
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

    public function byauthorAction(Request $request, $user_id, $page = 1)
    {
        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('cache_expiration'));

        $limit = 5;
        if ($page < 1) {
            $page = 1;
        }
        $start = ($page - 1) * $limit;

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AppBundle:User')->find($user_id);

        $pagetitle = "Card Reviews by " . $user->getUsername();

        $dql = "SELECT r FROM AppBundle:Review r WHERE r.user = :user ORDER BY r.date_creation DESC";
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

        return $this->render('AppBundle:Reviews:reviews.html.twig', array(
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

    public function commentAction(Request $request)
    {

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $fromEmail = $this->getParameter('email_sender_address');

        /* @var $user User */
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException("You are not logged in.");
        }

        $review_id = filter_var($request->get('comment_review_id'), FILTER_SANITIZE_NUMBER_INT);
        /* @var $review Review */
        $review = $em->getRepository('AppBundle:Review')->find($review_id);
        if (!$review) {
            throw new \Exception("Unable to find review.");
        }

        $comment_text = trim($request->get('comment'));
        $comment_text = htmlspecialchars($comment_text);
        if (!$comment_text) {
            throw new \Exception('Your comment is empty.');
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
                $spool[$review->getUser()->getEmail()] = 'AppBundle:Emails:newreviewcomment_author.html.twig';
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
            $message = Swift_Message::newInstance()
                    ->setSubject("[thronesdb] New review comment")
                    ->setFrom(array($fromEmail => $user->getUsername()))
                    ->setTo($email)
                    ->setBody($this->renderView($view, $email_data), 'text/html');
            $this->get('mailer')->send($message);
        }

        return new JsonResponse([
            'success' => true
        ]);
    }
}
