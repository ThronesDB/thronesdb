<?php

namespace AppBundle\Tests\Service;

use AppBundle\Entity\Card;
use AppBundle\Entity\Pack;
use AppBundle\Repository\CardRepository;
use AppBundle\Repository\PackRepository;
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
     * @var m\MockInterface;
     */
    protected $mockPackRepository;

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
        $this->mockPackRepository  = m::mock(PackRepository::class);
        $this->mockEntityManager
            ->shouldReceive('getRepository')
            ->with('AppBundle:Card')
            ->andReturn($this->mockCardRepository);
        $this->mockEntityManager
            ->shouldReceive('getRepository')
            ->with('AppBundle:Pack')
            ->andReturn($this->mockPackRepository);
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
        unset($this->mockPackRepository);
    }

    /**
     * @return array
     */
    public function testParseTextImportForCardWithPackNameProvider()
    {
        return [
            ["1x The Hand's Judgment (Core Set)", 1, "The Hand's Judgment", "Core Set"],
            ["2x Vengeance for Elia (Calm over Westeros)", 2, "Vengeance for Elia", "Calm over Westeros"],
            ["3x His Viper Eyes (Wolves of the North)", 3, "His Viper Eyes", "Wolves of the North"],
            ["1 Burning on the Sand (There Is My Claim)", 1, "Burning on the Sand", "There Is My Claim"],
            ["1 Secret Schemes (The Red Wedding)", 1, "Secret Schemes", "The Red Wedding"],
            [
                '2 "The Last of the Giants" (Watchers on the Wall)',
                2,
                '"The Last of the Giants"',
                "Watchers on the Wall"
            ],
        ];
    }

    /**
     * @covers \AppBundle\Services\DeckImportService::parseTextImport
     * @dataProvider testParseTextImportForCardWithPackNameProvider
     */
    public function testParseTextImportForCardWithPackName($input, $expectedQuantity, $expectedName, $expectedPackName)
    {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $pack = new Pack();
        $pack->setId(2000);
        $pack->setName($expectedPackName);
        $this->mockPackRepository->expects('findAll')->andReturn([ $pack ]);
        $this->mockCardRepository
            ->expects('findOneBy')
            ->with(['name' => $expectedName, 'pack' => $pack->getId()])
            ->andReturn($card);
        $data = $this->service->parseTextImport($input);
        $this->assertEquals($expectedQuantity, $data['content'][$cardCode]);
    }

    /**
     * @return array
     */
    public function testParseTextImportForCardWithPackCodeProvider()
    {
        return [
            ["1x The Hand's Judgment (Core)", 1, "The Hand's Judgment", "Core"],
            ["2x Vengeance for Elia (CoW)", 2, "Vengeance for Elia", "CoW"],
            ["3x His Viper Eyes (WotN)", 3, "His Viper Eyes", "WotN"],
            ["1 Burning on the Sand (TIMC)", 1, "Burning on the Sand", "TIMC"],
            ["1 Secret Schemes (TRW)", 1, "Secret Schemes", "TRW"],
            ['2 "The Last of the Giants" (WotW)', 2, '"The Last of the Giants"', "WotW"],
        ];
    }

    /**
     * @covers \AppBundle\Services\DeckImportService::parseTextImport
     * @dataProvider testParseTextImportForCardWithPackCodeProvider
     */
    public function testParseTextImportForCardWithPackCode($input, $expectedQuantity, $expectedName, $expectedPackCode)
    {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $pack = new Pack();
        $pack->setId(2000);
        $pack->setName('does not matter');
        $pack->setCode($expectedPackCode);
        $this->mockPackRepository->expects('findAll')->andReturn([ $pack ]);
        $this->mockCardRepository->expects('findOneBy')
            ->with(['name' => $expectedName, 'pack' => $pack->getId()])
            ->andReturn($card);
        $data = $this->service->parseTextImport($input);
        $this->assertEquals($expectedQuantity, $data['content'][$cardCode]);
    }

    /**
     * @return array
     */
    public function parseTextImportForCardWithoutPackInfoProvider()
    {
        return [
            ["1x The Hand's Judgment", 1, "The Hand's Judgment"],
            ["2x Vengeance for Elia", 2, "Vengeance for Elia"],
            ["3x His Viper Eyes", 3, "His Viper Eyes"],
            ["1 Burning on the Sand", 1, "Burning on the Sand"],
            ["1 Secret Schemes", 1, "Secret Schemes"],
            ["2 \"The Last of the Giants\"", 2, "\"The Last of the Giants\""],
        ];
    }

    /**
     * @covers \AppBundle\Services\DeckImportService::parseTextImport
     * @dataProvider parseTextImportForCardWithoutPackInfoProvider
     */
    public function testParseTextImportForCardWithoutPackInfo($input, $expectedQuantity, $expectedName)
    {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $this->mockPackRepository->expects('findAll')->andReturn([]);
        $this->mockCardRepository->expects('findOneBy')->with(['name' => $expectedName])->andReturn($card);
        $data = $this->service->parseTextImport($input);
        $this->assertEquals($expectedQuantity, $data['content'][$cardCode]);
    }
}
