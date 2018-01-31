<?php

namespace AppBundle\Services;

/**
 * Description of DeckImportService
 *
 * @author cedric
 */
class DeckImportService
{

    /**
     *
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function parseTextImport($text)
    {
        $data = [
            'content' => [],
            'faction' => null,
            'description' => ''
        ];

        $lines = explode("\n", $text);

        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\s*(\d)x?([\pLl\pLu\pN\-\.\'\!\: ]+)/u', $line, $matches)) {
                $quantity = intval($matches[1]);
                $name = trim($matches[2]);
            } elseif (preg_match('/^([^\(]+).*x(\d)/', $line, $matches)) {
                $quantity = intval($matches[2]);
                $name = trim($matches[1]);
            } elseif (preg_match('/^([^\(]+)/', $line, $matches)) {
                $quantity = 1;
                $name = trim($matches[1]);
            } else {
                continue;
            }
            $card = $this->em->getRepository('AppBundle:Card')->findOneBy(array(
                'name' => $name
            ));
            if ($card) {
                $data['content'][$card->getCode()] = $quantity;
            } else {
                $faction = $this->em->getRepository('AppBundle:Faction')->findOneBy(array(
                    'name' => $name
                ));
                if ($faction) {
                    $data['faction'] = $faction;
                }
            }
        }

        return $data;
    }

    public function parseOctgnImport($octgn)
    {
        $data = [
            'content' => [],
            'faction' => null,
            'description' => ''
        ];

        $crawler = new \Symfony\Component\DomCrawler\Crawler();
        $crawler->addXmlContent($octgn);

        // read octgnId
        $cardcrawler = $crawler->filter('deck > section > card');
        $octgnIds = [];
        foreach ($cardcrawler as $domElement) {
            $octgnIds[$domElement->getAttribute('id')] = intval($domElement->getAttribute('qty'));
        }

        // read desc
        $desccrawler = $crawler->filter('deck > notes');
        $descriptions = [];
        foreach ($desccrawler as $domElement) {
            $descriptions[] = $domElement->nodeValue;
        }
        $data['description'] = implode("\n", $descriptions);

        foreach ($octgnIds as $octgnId => $qty) {
            $card = $this->em->getRepository('AppBundle:Card')->findOneBy(array(
                'octgnId' => $octgnId
            ));
            if ($card) {
                $data['content'][$card->getCode()] = $qty;
            } else {
                $faction = $this->em->getRepository('AppBundle:Faction')->findOneBy(array(
                    'octgnId' => $octgnId
                ));
                if ($faction) {
                    $data['faction'] = $faction;
                }
            }
        }

        return $data;
    }
}
