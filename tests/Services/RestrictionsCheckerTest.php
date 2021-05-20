<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Card;
use App\Entity\CommonDeckInterface;
use App\Entity\Deck;
use App\Entity\Deckslot;
use App\Entity\Restriction;
use App\Entity\RestrictionInterface;
use App\Services\RestrictionsChecker;
use PHPUnit\Framework\TestCase;

/**
 * Class DeckImportServiceTest
 * @package App\Tests\Service
 */
class RestrictionsCheckerTest extends TestCase
{
    protected RestrictionsChecker $restrictionsChecker;

    protected RestrictionInterface $restriction;

    protected function setUp(): void
    {
        $this->restrictionsChecker = new RestrictionsChecker();
        $this->restriction = new Restriction();
        $contents = [
            'joust' => [
                'banned' => [
                    '01900',
                ],
                'restricted' => [
                    '01000',
                    '01005',
                    '01010',
                    '01015',
                ],
                'restricted_pods' => [
                    [
                        'title' => 'P1',
                        'restricted' => '01000',
                        'cards' => ['01001', '01002']
                    ],
                    [
                        'title' => 'P2',
                        'restricted' => '01005',
                        'cards' => ['01003', '01004']
                    ],
                    [
                        'title' => 'P3',
                        'cards' => ['01006', '01007']
                    ],
                    [
                        'title' => 'P4',
                        'cards' => ['01008', '01009']
                    ],
                ]
            ],
            'melee' => [
                'banned' => [
                    '01900',
                ],
                'restricted' => [
                    '01000',
                    '01005',
                    '01010',
                    '01015',
                ],
                'restricted_pods' => [
                    [
                        'title' => 'MP1',
                        'restricted' => '01000',
                        'cards' => ['01001', '01002']
                    ],
                    [
                        'title' => 'MP2',
                        'restricted' => '01005',
                        'cards' => ['01003', '01004']
                    ],
                    [
                        'title' => 'MP3',
                        'cards' => ['01006', '01007']
                    ],
                    [
                        'title' => 'MP4',
                        'cards' => ['01008', '01009']
                    ],
                ]
            ],
        ];
        $this->restriction->setContents($contents);
    }

    protected function tearDown(): void
    {
        unset($this->restriction);
        unset($this->restrictionsChecker);
    }

    public function isLegalProvider(): array
    {
        return [
            [['04001', '04002'], 'contains no restricted nor banned cards.'],
            [['04001', '04002', '01000'], 'contains one restricted card'],
            [['01000', '01003', '01004'], 'contains one restricted card and cards from a different restricted-pod.'],
            [['01001', '01002'], 'contains cards in restricted-pod but not the restricted card itself.'],
            [['01006', '01008'], 'contains cards in pods but only one from each pod max.'],
            [[], 'empty list']
        ];
    }

    /**
     * @covers \App\Services\RestrictionsChecker::isLegalForJoust
     * @dataProvider isLegalProvider
     * @param array $codes
     * @param string $message
     */
    public function testIsLegalForJoust(array $codes, string $message): void
    {
        $deck = $this->buildDeck($codes);
        $this->assertTrue($this->restrictionsChecker->isLegalForJoust($this->restriction, $deck), $message);
    }

    /**
     * @covers \App\Services\RestrictionsChecker::isLegalForMelee
     * @dataProvider isLegalProvider
     * @param array $codes
     * @param string $message
     */
    public function testIsLegalForMelee(array $codes, string $message): void
    {
        $deck = $this->buildDeck($codes);
        $this->assertTrue($this->restrictionsChecker->isLegalForMelee($this->restriction, $deck), $message);
    }


    public function isNotLegalProvider(): array
    {
        return [
            [['04001', '04002', '01900'], 'contains banned card'],
            [['04001', '04002', '01000', '01005'], 'contains more than one restricted card'],
            [['01000', '01001'], 'contains restricted card and a card in its restricted-pod.'],
            [['01006', '01007'], 'contains more than one card from a pod.'],
        ];
    }

    /**
     * @covers \App\Services\RestrictionsChecker::isLegalForJoust
     * @dataProvider isNotLegalProvider
     * @param array $codes
     * @param string $message
     */
    public function testIsNotLegalForJoust(array $codes, string $message): void
    {
        $deck = $this->buildDeck($codes);
        $this->assertFalse($this->restrictionsChecker->isLegalForJoust($this->restriction, $deck), $message);
    }

    /**
     * @covers \App\Services\RestrictionsChecker::isLegalForMelee
     * @dataProvider isNotLegalProvider
     * @param array $codes
     * @param string $message
     */
    public function testIsNotLegalForMelee(array $codes, string $message): void
    {
        $deck = $this->buildDeck($codes);
        $this->assertFalse($this->restrictionsChecker->isLegalForMelee($this->restriction, $deck), $message);
    }

    protected function buildDeck(array $codes): CommonDeckInterface
    {
        $deck = new Deck();
        foreach ($codes as $code) {
            $card = new Card();
            $card->setCode($code);
            $slot = new Deckslot();
            $slot->setCard($card);
            $deck->addSlot($slot);
        }
        return $deck;
    }
}
