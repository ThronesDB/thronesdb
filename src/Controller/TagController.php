<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Deck;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @package App\Controller
 */
class TagController extends AbstractController
{
    public function __construct(private readonly ManagerRegistry $managerRegistry)
    {
    }
    /**
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/tag/add', name: 'tag_add', methods: ['POST'])]
    public function addAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var EntityManager $em */
        $em = $this->managerRegistry->getManager();

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
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/tag/remove', name: 'tag_remove', methods: ['POST'])]
    public function removeAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var EntityManager $em */
        $em = $this->managerRegistry->getManager();

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
     *
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    #[Route(path: '/tag/clear', name: 'tag_clear', methods: ['POST'])]
    public function clearAction(Request $request)
    {
        $list_id = $request->get('ids');

        /* @var EntityManager $em */
        $em = $this->managerRegistry->getManager();

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
