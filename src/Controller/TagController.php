<?php
namespace App\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Deck;
use Symfony\Component\HttpFoundation\Response;

class TagController extends Controller
{
    public function addAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var $deck Deck */
            $deck = $em->getRepository(Deck::class)->find($id);
            if (!$deck) {
                continue;
            }
            if ($this->getUser()->getId() != $deck->getUser()->getId()) {
                continue;
            }
            $tags = array_unique(array_values(array_merge(preg_split('/\s+/', $deck->getTags()), $list_tag)));
            $response['tags'][$deck->getId()] = $tags;
            $deck->setTags(implode(' ', $tags));
        }
        $em->flush();

        return new Response(json_encode($response));
    }

    public function removeAction(Request $request)
    {
        $list_id = $request->get('ids');
        $list_tag = $request->get('tags');

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var $deck Deck */
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

    public function clearAction(Request $request)
    {
        $list_id = $request->get('ids');

        /* @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $response = array("success" => true);

        foreach ($list_id as $id) {
            /* @var $deck Deck */
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
