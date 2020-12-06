<?php

namespace App\Services;

use App\Entity\Card;
use App\Entity\Faction;
use App\Entity\Pack;
use App\Entity\PackInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of DeckImportService
 *
 * @author cedric
 */
class DeckImportService
{
    // three equal signs (or more) in a row are used
    // to separate multiple decks in one upload
    const DECKS_DELIMITER_REGEXP = '/[=]{3,}/';

    // <quantity>x<card name> (<pack code|name>)
    // <quantity><card name> (<pack code|name>)
    const CARD_WITH_PACK_INFO_REGEXP = '/^\s*(\d)x?([^(]+) \(([^)]+)/u';

    // <quantity>x<card name>
    // <quantity><card name>
    const CARD_WITHOUT_PACK_INFO_REGEXP = '/^\s*(\d)x?([\pLl\pLu\pN\-.\'!:" ]+)/u';

    // #<three digits> <quantity>x<card name>
    // #<three digits> <quantity><card name>
    const CARD_WITHOUT_PACK_INFO_ALT1_REGEXP = '/^\s*#\d{3}\s(\d)x?([\pLl\pLu\pN\-.\'!: ]+)/u';

    // <card name>x<quantity>
    const CARD_WITHOUT_PACK_INFO_ALT2_REGEXP = '/^([^(]+).*x(\d)/';

    // <card name>
    const SINGLE_CARD_WITHOUT_PACK_INFO_REGEXP = '/^([^(]+)/';

    // <card name> (<pack code|name>)
    const SINGLE_CARD_WITH_PACK_INFO_REGEXP = '/^([^\(]+) \(([^)]+)/u';

    protected EntityManagerInterface $em;

    protected TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @param string $text
     * @return array
     */
    public function parseTextImport(string $text): array
    {
        $rhett = [
            'decks' => [],
            'errors' => [],
        ];

        $text = trim($text);
        if ('' === $text) {
            return $rhett;
        }

        $textChunks = preg_split(self::DECKS_DELIMITER_REGEXP, $text, null, PREG_SPLIT_NO_EMPTY);

        $decks = [];

        // trim whitespace off of all lines and filter out any blank lines
        $removeFiller = function (array $lines) {
            $lines = array_map(function ($line) {
                return trim($line);
            }, $lines);
            $lines = array_filter($lines, function ($line) {
                return '' !== $line;
            });
            return array_values($lines);
        };

        foreach ($textChunks as $text) {
            $lines = explode("\n", trim($text));
            $lines = $removeFiller($lines);

            if (!empty($lines)) {
                $decks[] = $lines;
            }
        }

        if (empty($decks)) {
            return $rhett;
        }

        // load all packs upfront and map them by their names and codes for easy lookup below
        $packs = $this->em->getRepository(Pack::class)->findAll();
        $packsByName = array_combine(array_map(function (PackInterface $pack) {
            return $pack->getName();
        }, $packs), $packs);
        $packsByCode = array_combine(array_map(function (PackInterface $pack) {
            return $pack->getCode();
        }, $packs), $packs);

        foreach ($decks as $lines) {
            try {
                $rhett['decks'][] = $this->parseOneTextImport($lines, $packsByName, $packsByCode);
            } catch (Exception $e) {
                $rhett['errors'][] = $e->getMessage();
            }
        }

        return $rhett;
    }

    /**
     * @param array $lines
     * @param array $packsByName
     * @param array $packsByCode
     * @return array
     * @throws Exception
     */
    protected function parseOneTextImport(array $lines, array $packsByName, array $packsByCode): array
    {
        $data = [
            'content' => [],
            'faction' => null,
            'description' => '',
            'name' => 'new deck',
        ];

        // set the deck's name from the first line in the given import
        $data['name'] = $lines[0];

        foreach ($lines as $line) {
            $matches = [];
            $packNameOrCode = null;
            $card = null;

            if (preg_match(self::CARD_WITH_PACK_INFO_REGEXP, $line, $matches)) {
                $quantity = intval($matches[1]);
                $name = trim($matches[2]);
                $packNameOrCode = trim($matches[3]);
            } elseif (preg_match(self::CARD_WITHOUT_PACK_INFO_REGEXP, $line, $matches)) {
                $quantity = intval($matches[1]);
                $name = trim($matches[2]);
            } elseif (preg_match(self::CARD_WITHOUT_PACK_INFO_ALT1_REGEXP, $line, $matches)) {
                $quantity = intval($matches[1]);
                $name = trim($matches[2]);
            } elseif (preg_match(self::CARD_WITHOUT_PACK_INFO_ALT2_REGEXP, $line, $matches)) {
                $quantity = intval($matches[2]);
                $name = trim($matches[1]);
            } elseif (preg_match(self::SINGLE_CARD_WITH_PACK_INFO_REGEXP, $line, $matches)) {
                $quantity = 1;
                $name = trim($matches[1]);
                $packNameOrCode = trim($matches[2]);
            } elseif (preg_match(self::SINGLE_CARD_WITHOUT_PACK_INFO_REGEXP, $line, $matches)) {
                $quantity = 1;
                $name = trim($matches[1]);
            } else {
                continue;
            }

            if ($packNameOrCode) {
                /* @var PackInterface $pack */
                $pack = null;
                if (array_key_exists($packNameOrCode, $packsByName)) {
                    $pack = $packsByName[$packNameOrCode];
                } elseif (array_key_exists($packNameOrCode, $packsByCode)) {
                    $pack = $packsByCode[$packNameOrCode];
                }
                if ($pack) {
                    $card = $this->em->getRepository(Card::class)->findOneBy(array(
                        'name' => $name,
                        'pack' => $pack->getId(),
                    ));
                }
            } else {
                $card = $this->em->getRepository(Card::class)->findOneBy(array(
                    'name' => $name
                ));
            }

            if ($card) {
                $data['content'][$card->getCode()] = $quantity;
            } else {
                $faction = $this->em->getRepository(Faction::class)->findOneBy(array(
                    'name' => $name
                ));
                if ($faction) {
                    $data['faction'] = $faction;
                }
            }
        }

        if (empty($data['faction'])) {
            throw new Exception($this->translator->trans('decks.import.error.cannotFindFaction'));
        }

        return $data;
    }
}
