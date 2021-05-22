<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Card;
use App\Entity\Faction;
use App\Entity\Pack;
use App\Repository\CardRepository;
use App\Repository\FactionRepository;
use App\Repository\PackRepository;
use App\Services\DeckImportService;
use Doctrine\ORM\EntityManager;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class DeckImportServiceTest
 * @package App\Tests\Service
 */
class DeckImportServiceTest extends TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    protected MockInterface $mockEntityManager;
    protected MockInterface $mockCardRepository;
    protected MockInterface $mockPackRepository;
    protected MockInterface $mockFactionRepository;
    protected MockInterface $mockTranslator;
    protected DeckImportService $service;

    protected function setUp(): void
    {
        $this->mockEntityManager = Mockery::mock(EntityManager::class);
        $this->mockFactionRepository = Mockery::mock(FactionRepository::class);
        $this->mockCardRepository = Mockery::mock(CardRepository::class);
        $this->mockPackRepository  = Mockery::mock(PackRepository::class);
        $this->mockEntityManager
            ->shouldReceive('getRepository')
            ->with(Card::class)
            ->andReturn($this->mockCardRepository);
        $this->mockEntityManager
            ->shouldReceive('getRepository')
            ->with(Pack::class)
            ->andReturn($this->mockPackRepository);
        $this->mockEntityManager
            ->shouldReceive('getRepository')
            ->with(Faction::class)
            ->andReturn($this->mockFactionRepository);
        $this->mockTranslator = Mockery::mock(TranslatorInterface::class);
        $this->service = new DeckImportService($this->mockEntityManager, $this->mockTranslator);
    }

    protected function tearDown(): void
    {
        unset($this->service);
        unset($this->mockEntityManager);
        unset($this->mockCardRepository);
        unset($this->mockPackRepository);
        unset($this->mockFactionRepository);
        unset($this->mockTranslator);
    }

    /**
     * @return array
     */
    public function testParseTextImportForCardWithPackNameProvider(): array
    {
        return [
            [
                "House Baratheon\n1x The Hand's Judgment (Core Set)",
                1,
                "The Hand's Judgment",
                "Core Set",
                "House Baratheon",
            ],
            [
                "House Martell\n2x Vengeance for Elia (Calm over Westeros)",
                2,
                "Vengeance for Elia",
                "Calm over Westeros",
                "House Martell",
            ],
            [
                "House Martell\n3x His Viper Eyes (Wolves of the North)",
                3,
                "His Viper Eyes",
                "Wolves of the North",
                "House Martell",
            ],
            [
                "House Martell\n1 Burning on the Sand (There Is My Claim)",
                1,
                "Burning on the Sand",
                "There Is My Claim",
                "House Martell"
            ],
            [
                "House Martell\n1 Secret Schemes (The Red Wedding)",
                1,
                "Secret Schemes",
                "The Red Wedding",
                "House Martell",
            ],
            [
                "House Baratheon\n2 \"The Last of the Giants\" (Watchers on the Wall)",
                2,
                '"The Last of the Giants"',
                "Watchers on the Wall",
                "House Baratheon",
            ],
            ["House Stark\nSea of Blood (Redesigns)", 1, 'Sea of Blood', "Redesigns", "House Stark"],
        ];
    }

    /**
     * @covers       \App\Services\DeckImportService::parseTextImport
     * @dataProvider testParseTextImportForCardWithPackNameProvider
     * @param string $input
     * @param int $expectedQuantity
     * @param string $expectedName
     * @param string $expectedPackName
     * @param string $expectedFaction
     */
    public function testParseTextImportForCardWithPackName(
        string $input,
        int $expectedQuantity,
        string $expectedName,
        string $expectedPackName,
        string $expectedFaction
    ): void {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $faction = new Faction();
        $faction->setName($expectedFaction);
        $pack = new Pack();
        $pack->setId(2000);
        $pack->setName($expectedPackName);
        $this->mockPackRepository->shouldReceive('findAll')->andReturn([ $pack ]);
        $this->mockCardRepository
            ->shouldReceive('findOneBy')
            ->with(['name' => $expectedName, 'pack' => $pack->getId()])
            ->andReturn($card);
        $this->mockCardRepository->shouldReceive('findOneBy')->andReturn(null);
        $this->mockFactionRepository
            ->shouldReceive('findOneBy')
            ->with(['name' => $expectedFaction])
            ->andReturn($faction);
        $data = $this->service->parseTextImport($input);
        $this->assertEmpty($data['errors']);
        $this->assertEquals(1, count($data['decks']));
        $this->assertEquals($expectedQuantity, $data['decks'][0]['content'][$cardCode]);
        $this->assertEquals($faction, $data['decks'][0]['faction']);
    }

    public function testParseTextImportForCardWithPackCodeProvider(): array
    {
        return [
            ["House Targaryen\n1x The Hand's Judgment (Core)", 1, "The Hand's Judgment", "Core", "House Targaryen"],
            ["House Martell\n2x Vengeance for Elia (CoW)", 2, "Vengeance for Elia", "CoW", "House Martell"],
            ["House Martell\n3x His Viper Eyes (WotN)", 3, "His Viper Eyes", "WotN", "House Martell"],
            ["House Martell\n1 Burning on the Sand (TIMC)", 1, "Burning on the Sand", "TIMC", "House Martell"],
            ["House Martell\n1 Secret Schemes (TRW)", 1, "Secret Schemes", "TRW", "House Martell"],
            ["House Stark\n2 \"The Last of the Giants\" (WotW)", 2, '"The Last of the Giants"', "WotW", "House Stark"],
            ["House Stark\nSea of Blood (R)", 1, 'Sea of Blood', "R", "House Stark"],
        ];
    }

    /**
     * @covers       \App\Services\DeckImportService::parseTextImport
     * @dataProvider testParseTextImportForCardWithPackCodeProvider
     * @param string $input
     * @param int $expectedQuantity
     * @param string $expectedName
     * @param string $expectedPackCode
     * @param string $expectedFaction
     */
    public function testParseTextImportForCardWithPackCode(
        string $input,
        int $expectedQuantity,
        string $expectedName,
        string $expectedPackCode,
        string $expectedFaction
    ): void {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $pack = new Pack();
        $faction = new Faction();
        $pack->setId(2000);
        $pack->setName('does not matter');
        $pack->setCode($expectedPackCode);
        $this->mockPackRepository->shouldReceive('findAll')->andReturn([ $pack ]);
        $this->mockCardRepository->shouldReceive('findOneBy')
            ->with(['name' => $expectedName, 'pack' => $pack->getId()])
            ->andReturn($card);
        $this->mockCardRepository->shouldReceive('findOneBy')->andReturn(null);
        $this->mockFactionRepository
            ->shouldReceive('findOneBy')
            ->with(['name' => $expectedFaction])
            ->andReturn($faction);
        $data = $this->service->parseTextImport($input);
        $this->assertEmpty($data['errors']);
        $this->assertEquals(1, count($data['decks']));
        $this->assertEquals($expectedQuantity, $data['decks'][0]['content'][$cardCode]);
        $this->assertEquals($faction, $data['decks'][0]['faction']);
    }

    public function parseTextImportForCardWithoutPackInfoProvider(): array
    {
        return [
            ["House Stark\n1x The Hand's Judgment", 1, "The Hand's Judgment", "House Stark"],
            ["House Martell\n2x Vengeance for Elia", 2, "Vengeance for Elia", "House Martell"],
            ["House Martell\n3x His Viper Eyes", 3, "His Viper Eyes", "House Martell"],
            ["House Martell\n1 Burning on the Sand", 1, "Burning on the Sand", "House Martell"],
            ["House Martell\n1 Secret Schemes", 1, "Secret Schemes", "House Martell"],
            ["House Lannister\n2 \"The Last of the Giants\"", 2, "\"The Last of the Giants\"", "House Lannister"],
        ];
    }

    /**
     * @covers       \App\Services\DeckImportService::parseTextImport
     * @dataProvider parseTextImportForCardWithoutPackInfoProvider
     * @param string $input
     * @param int $expectedQuantity
     * @param string $expectedName
     * @param string $expectedFaction
     */
    public function testParseTextImportForCardWithoutPackInfo(
        string $input,
        int $expectedQuantity,
        string $expectedName,
        string $expectedFaction
    ) {
        $cardCode = "does-not-matter";
        $card = new Card();
        $card->setCode($cardCode);
        $faction = new Faction();
        $this->mockPackRepository->shouldReceive('findAll')->andReturn([]);
        $this->mockCardRepository
            ->shouldReceive('findOneBy')
            ->with(['name' => $expectedName])
            ->andReturn($card);
        $this->mockCardRepository->shouldReceive('findOneBy')->andReturn(null);
        $this->mockFactionRepository
            ->shouldReceive('findOneBy')
            ->with(['name' => $expectedFaction])
            ->andReturn($faction);
        $data = $this->service->parseTextImport($input);
        $this->assertEmpty($data['errors']);
        $this->assertEquals(1, count($data['decks']));
        $this->assertEquals($expectedQuantity, $data['decks'][0]['content'][$cardCode]);
        $this->assertEquals($faction, $data['decks'][0]['faction']);
    }

    public function parseTextImportIgnoreEmptyInputProvider(): array
    {
        return [
            [""],
            ["    "],
            ["\n \r\n "],
            ["==="],
            [" ==== "],
            [" \n===\n \n=== ===\n "],
        ];
    }

    /**
     * @covers       \App\Services\DeckImportService::parseTextImport
     * @dataProvider parseTextImportIgnoreEmptyInputProvider
     * @param string $input
     */
    public function testParseTextImportIgnoreEmptyInput(string $input): void
    {
        $data = $this->service->parseTextImport($input);
        $this->assertEmpty($data['decks']);
        $this->assertEmpty($data['errors']);
    }

    /**
     * @covers \App\Services\DeckImportService::parseTextImport
     */
    public function testParseTextImportMultipleDecks(): void
    {
        $text = <<<EOL
        Rightful Ruler Deck
        House Baratheon
        3x Stannis Baratheon
        ======
        Trucker Bomb Deck
        House Lannister
        2x The Kingsroad
        === 
        Bad Idea Machine
        The Night's Watch
        Aloof and Apart
        1x Catapult at the Wall
        2x Ghost
        EOL;

        $pack = new Pack();
        $knownFactions = ['House Baratheon', "The Night's Watch", 'House Lannister'];
        $knownCards = ['The Kingsroad', 'Aloof and Apart', 'Stannis Baratheon', 'Catapult at the Wall', 'Ghost'];
        $this->mockPackRepository->shouldReceive('findAll')->andReturn([ $pack ]);

        $this->mockFactionRepository
            ->shouldReceive('findOneBy')
            ->andReturnUsing(function (array $args) use ($knownFactions) {
                if (in_array($args['name'], $knownFactions)) {
                    $faction = new Faction();
                    $faction->setName($args['name']);
                    return $faction;
                }
                return null;
            });

        $this->mockCardRepository
            ->shouldReceive('findOneBy')
            ->andReturnUsing(function (array $args) use ($knownCards) {
                if (in_array($args['name'], $knownCards)) {
                    $card = new Card();
                    $card->setCode($args['name']);
                    return $card;
                }
                return null;
            });
        $data = $this->service->parseTextImport($text);
        $this->assertCount(0, $data['errors']);
        $this->assertCount(3, $data['decks']);
        $this->assertEquals('Rightful Ruler Deck', $data['decks'][0]['name']);
        $this->assertEquals('House Baratheon', $data['decks'][0]['faction']->getName());
        $this->assertCount(1, $data['decks'][0]['content']);
        $this->assertEquals(3, $data['decks'][0]['content']['Stannis Baratheon']);
        $this->assertEquals('Trucker Bomb Deck', $data['decks'][1]['name']);
        $this->assertEquals('House Lannister', $data['decks'][1]['faction']->getName());
        $this->assertCount(1, $data['decks'][1]['content']);
        $this->assertEquals(2, $data['decks'][1]['content']['The Kingsroad']);
        $this->assertEquals('Bad Idea Machine', $data['decks'][2]['name']);
        $this->assertEquals("The Night's Watch", $data['decks'][2]['faction']->getName());
        $this->assertCount(3, $data['decks'][2]['content']);
        $this->assertEquals(1, $data['decks'][2]['content']['Catapult at the Wall']);
        $this->assertEquals(2, $data['decks'][2]['content']['Ghost']);
        $this->assertEquals(1, $data['decks'][2]['content']['Aloof and Apart']);
    }

    /**
     * @covers \App\Services\DeckImportService::parseTextImport
     */
    public function testParseTextImportFailsIfFactionCannotBeIdentified(): void
    {
        $text = <<<EOL
        Some lines
        of 
        text.
        Doesn't really matter, 
        what it is here.
        EOL;

        $pack = new Pack();
        $card = new Card();
        $card->setCode('whatever');
        $this->mockTranslator
            ->shouldReceive('trans')
            ->with('decks.import.error.cannotFindFaction')
            ->andReturn('Unable to find the faction of the deck.')
            ->once();
        $this->mockPackRepository->shouldReceive('findAll')->andReturn([ $pack ]);
        $this->mockCardRepository->shouldReceive('findOneBy')->andReturn(null)->times(5);
        $this->mockFactionRepository->shouldReceive('findOneBy')->andReturn(null)->times(5);
        $data = $this->service->parseTextImport($text);
        $this->assertEmpty($data['decks']);
        $this->assertCount(1, $data['errors']);
        $this->assertEquals('Unable to find the faction of the deck.', $data['errors'][0]);
    }
}
