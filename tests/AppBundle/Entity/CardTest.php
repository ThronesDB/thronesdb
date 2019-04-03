<?php

namespace AppBundle\Tests\Entity;

use AppBundle\Entity\Card;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;

/**
 * Class CardTest
 * @package AppBundle\Tests\Entity
 * @covers Card
 */
class CardTest extends TestCase
{

    public function hasShadowKeywordProvider()
    {
        return [
            ['', 'Shadow', false],
            ['Shadow', 'Shadow', false],
            ['Shadow (X)', 'Shadow', false],
            ['Shadow (x).', 'Shadow', false],
            ['Shadow (A).', 'Shadow', false],
            ['Shadow (5X).', 'Shadow', false],
            ['Shadow (99).', 'Schatten', false],
            ["something else on first line\nShadow (99).", 'Shadow', false],
            ['Shadow (X).', 'Shadow', true],
            ['Shadow (X). Stealth.', 'Shadow', true],
            ['Ambush (0). Shadow (X). Stealth.', 'Shadow', true],
            ['Shadow (0).', 'Shadow', true],
            ['Shadow (5).', 'Shadow', true],
            ['Shadow (99).', 'Shadow', true],
            ['Schatten (99).', 'Schatten', true],
            ["Shadow (99). and other stuff on first line\nlorem ipsum", 'Shadow', true],
        ];
    }
    /**
     * @covers Card::hasShadowKeyword
     * @dataProvider hasShadowKeywordProvider
     */
    public function testHasShadowKeyword($text, $shadowKeyword, $hasKeyword)
    {
        $card = new Card();
        $card->setText($text);
        $this->assertEquals($card->hasShadowKeyword($shadowKeyword), $hasKeyword);
    }
}
