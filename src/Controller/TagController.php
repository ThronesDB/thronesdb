<?php

namespace App\Controller;

use App\Entity\Deck;
use App\Entity\DeckInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @package App\Controller
 */
class TagController extends AbstractController
{
    /**
     * @Route("/tag/add", name="tag_add", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function addAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var DeckInterface $deck */
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $tags = array_unique(
                array_values(
                    array_merge(
                        preg_split(
                            '/\s+/',
                            $deck->getTags()
                        ),
                        $list_tag
                    )
                )
            );
            array_filter($tags);
            $deck->setTags(implode(' ', $tags));
            $response['tags'][$deck->getId()] = explode(' ', $deck->getTags());
        }
        $em->flush();

        return new Response(json_encode($response));
    }

    /**
     * @Route("/tag/remove", name="tag_remove", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var DeckInterface $deck */
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $tags = array_values(array_diff(preg_split('/\s+/', $deck->getTags()), $list_tag));
            $response['tags'][$deck->getId()] = $tags;
            $deck->setTags(implode(' ', $tags));
        }
        $em->flush();

        return new Response(json_encode($response));
    }

    /**
     * @Route("/tag/clear", name="tag_clear", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function clearAction(Request $request)
    {
        $list_id = $request->get('ids');

        /* @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var DeckInterface $deck */
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $response['tags'][$deck->getId()] = [];
            $deck->setTags('');
        }
        $em->flush();

        return new Response(json_encode($response));
    }
}
