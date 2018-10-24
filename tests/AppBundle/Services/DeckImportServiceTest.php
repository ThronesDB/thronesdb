<?php

namespace AppBundle\Tests\Service;

use AppBundle\Entity\Card;
use AppBundle\Repository\CardRepository;
use AppBundle\Services\DeckImportService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Tests\TestCase;
use Mockery as m;

/**
 * Class DeckImportServiceTest
 * @package AppBundle\Tests\Service
 */
class DeckImportServiceTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /**
     * @var m\MockInterface;
     */
    protected $mockEntityManager;

    /**
     * @var m\MockInterface;
     */
    protected $mockCardRepository;
    /**
     * @var DeckImportService $service
     */
    protected $service;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->mockEntityManager = m::mock(EntityManager::class);
        $this->mockCardRepository = m::mock(CardRepository::class);
        $this->mockEntityManager->expects('getRepository')->with('AppBundle:Card')->andReturn($this->mockCardRepository);
        $this->service = new DeckImportService($this->mockEntityManager);
    }

    /**
     * @inheritdoc
     */
    public function tearDown()
    {
        unset($this->service);
        unset($this->mockEntityManager);
        unset($this->mockCardRepository);
    }

    /**
     * @return array
     */
    public function parseTextImportForCardProvider()
    {
        return [
            ["1x The Hand's Judgment (Core Set)", 1, "The Hand's Judgment"],
            ["2x Vengeance for Elia (Calm over Westeros)", 2, "Vengeance for Elia"],
            ["3x His Viper Eyes (Wolves of the North)", 3, "His Viper Eyes"],
            ["1x Burning on the Sand (There Is My Claim)", 1, "Burning on the Sand"],
            ["1x Secret Schemes (The Red Wedding)", 1, "Secret Schemes"],
            ["2x \"The Last of the Giants\" (Watchers on the Wall)", 2, "\"The Last of the Giants\""],
        ];
    }

    /**
     * @covers \AppBundle\Services\DeckImportService::parseTextImport
     * @dataProvider parseTextImportForCardProvider
     */
    public function testParseTextImportForCard($input, $expectedQuantity, $expectedName)
    {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $this->mockCardRepository->expects('findOneBy')->with(['name' => $expectedName])->andReturn($card);
        $data = $this->service->parseTextImport($input);
        $this->assertEquals($expectedQuantity, $data['content'][$cardCode]);
    }
}
