<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardInterface;
use App\Entity\Cycle;
use App\Entity\Faction;
use App\Entity\Pack;
use App\Entity\Type;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GlobIterator;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Card data importer.
 *
 * @package App\Command
 */
class ImportStdCommand extends Command
{
    const PACKS_SUBDIRECTORY_NAME = 'packs';

    protected array $collections = [];

    protected EntityManagerInterface  $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:import:std')
            ->setDescription(
                'Import cards from a local thronteki-json-data repository.'
            )
            ->addArgument(
                'path',
                InputArgument::REQUIRED,
                'Path to your local throneteki-json-data repository.'
            );
    }
    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = $input->getArgument('path');
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        /* @var $helper QuestionHelper */
        $helper = $this->getHelper('question');

        // load factions and card types
        $this->collections['Faction'] = $this->loadCollection(Faction::class);
        $this->collections['Type'] = $this->loadCollection(Type::class);

        // cycles
        $output->writeln("Importing Cycles...");
        $cyclesFileInfo = $this->getFileInfo($path, 'cycles.json');
        $rawCyclesData = $this->getDataFromFile($cyclesFileInfo);
        $imported = $this->importCyclesJsonFile($rawCyclesData, $output);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (! $helper->ask($input, $output, $question)) {
                return 0;
            }
        }
        $this->entityManager->flush();
        $this->collections['Cycle'] = $this->loadCollection(Cycle::class);
        $output->writeln("Done.");

        $packsMap = $this->mapPacksToCycles($rawCyclesData);

        // second, read raw packs and cards data
        $rawPacksData = [];
        $output->writeln("Importing Packs...");
        $fileSystemIterator = $this->getPacksDirectoryFilesIterator($path);
        $imported = [];
        foreach ($fileSystemIterator as $fileinfo) {
            $baseName = $fileinfo->getBasename('.json');
            $rawPacksData[$baseName] = $this->getDataFromFile($fileinfo);
            $imported = array_merge(
                $imported,
                $this->importPacksJsonFile($rawPacksData[$baseName], $packsMap, $output)
            );
        }
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (! $helper->ask($input, $output, $question)) {
                return 0;
            }
        }

        $this->entityManager->flush();
        $this->collections['Pack'] = $this->loadCollection(Pack::class);
        $output->writeln("Done.");

        // third, cards
        $output->writeln("Importing Cards...");
        $sortedCycles = $this->groupPacksInCycles($rawCyclesData, $rawPacksData);
        $multiNames = $this->extractCardNamesWithMultipleInstances($rawPacksData);

        $imported = $this->importCards($sortedCycles, $multiNames, $output);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (! $helper->ask($input, $output, $question)) {
                return 0;
            }
        }
        $this->entityManager->flush();
        $output->writeln("Done.");
        return 0;
    }

    /**
    /**
     * @param array $list
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function importCyclesJsonFile(array $list, OutputInterface $output)
    {
        $result = [];
        $position = 1;
        foreach ($list as $item) {
            $data = [];
            $data['code'] = $item['id'];
            $data['position'] = $position;
            $data['size'] = count($item['packs']);
            $data['name'] = $item['name'];
            $entity = $this->getEntityFromData(Cycle::class, $data, [
                'name',
                'code',
                'position',
                'size'
            ], [], [], $output);
            if ($entity) {
                $result[] = $entity;
                $this->entityManager->persist($entity);
            }
            $position++;
        }

        return $result;
    }

    /**
     * @param array $pack
     * @param array $map
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function importPacksJsonFile(array $pack, array $map, OutputInterface $output)
    {
        $result = [];
        $data = [];
        $data['cgdb_id'] = $pack['cgdbId'];
        $data['code'] = $pack['code'];
        $data['name'] = $pack['name'];
        $data['date_release'] = $pack['releaseDate'];
        $data['size'] = count($pack['cards']);
        $data['position'] = $map[$pack['code']]['position'];
        $data['cycle_code'] = $map[$pack['code']]['cycle'];

        $entity = $this->getEntityFromData(Pack::class, $data, [
            'name',
            'code',
            'position',
            'size',
            'date_release',
            'cgdb_id'
        ], [
            'cycle_code'
        ], [], $output);
        if ($entity) {
            $result[] = $entity;
            $this->entityManager->persist($entity);
        }
        return $result;
    }

    /**
     * @param array $cycles
     * @param array $multiNames
     * @param OutputInterface $output
     * @return array
     * @throws Exception
     */
    protected function importCards(array $cycles, array $multiNames, OutputInterface $output)
    {
        $result = [];
        foreach ($cycles as $packs) {
            $position = 1;
            foreach ($packs as $pack) {
                foreach ($pack['cards'] as $item) {
                    $data = [];
                    $data['code'] = $item['code'];
                    $data['cost'] = array_key_exists('cost', $item) ? (string)$item['cost'] : null;
                    $data['deck_limit'] = $item['deckLimit'];
                    $data['designer'] = array_key_exists('designer', $item) ? $item['designer'] : null;
                    $data['faction_code'] = $item['faction'];
                    $data['flavor'] = array_key_exists('flavor', $item) ? $item['flavor'] : '';
                    $data['illustrator'] = array_key_exists('illustrator', $item) ? $item['illustrator'] : null;
                    $data['is_loyal'] = array_key_exists('loyal', $item) ? $item['loyal'] : false;
                    $data['is_multiple'] = in_array($item['name'], $multiNames);
                    $data['is_unique'] = array_key_exists('unique', $item) ? $item['unique'] : false;
                    $data['name'] = $item['name'];
                    $data['octgn_id'] = array_key_exists('octgnId', $item) ? $item['octgnId'] : null;
                    $data['pack_code'] = $pack['code'];
                    $data['position'] = $position;
                    $data['quantity'] = $item['quantity'];
                    $data['text'] = $item['text'];
                    // @todo Replace this stop-gap solution and import errata properly. [ST 2020/04/12]
                    if (array_key_exists('errata', $item) && $item['errata']) {
                        $data['text'] .= "\n<em>Errata'd.</em>";
                    }
                    $data['traits'] = $item['traits'] ? implode('. ', $item['traits']) . '.' : '';
                    $data['type_code'] = $item['type'];
                    $data['strength'] = null;
                    if (array_key_exists('strength', $item)) {
                        $data['strength'] = $this->convertXValue($item['strength']);
                    }
                    if (array_key_exists('plotStats', $item)) {
                        $plotStats = $item['plotStats'];
                        $data['income'] = $this->convertXValue($plotStats['income']);
                        $data['initiative'] = $this->convertXValue($plotStats['initiative']);
                        $data['reserve'] = $this->convertXValue($plotStats['reserve']);
                        $data['claim'] = $this->convertXValue($plotStats['claim']);
                    }
                    if (array_key_exists('icons', $item)) {
                        $icons = $item['icons'];
                        $data['is_military'] = $icons['military'];
                        $data['is_intrigue'] = $icons['intrigue'];
                        $data['is_power'] = $icons['power'];
                    }

                    $position++;

                    $entity = $this->getEntityFromData(
                        Card::class,
                        $data,
                        [
                            'name',
                            'code',
                            'deck_limit',
                            'position',
                            'quantity',
                            'text',
                            'flavor',
                            'is_loyal',
                            'is_unique',
                            'is_multiple'
                        ],
                        [
                            'faction_code',
                            'pack_code',
                            'type_code'
                        ],
                        [
                            'designer',
                            'illustrator',
                            'traits',
                            'cost',
                            'octgn_id'
                        ],
                        $output
                    );
                    if ($entity) {
                        $result[] = $entity;
                        $this->entityManager->persist($entity);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $fieldName
     * @param $newJsonValue
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function copyFieldValueToEntity($entity, $entityName, $fieldName, $newJsonValue, OutputInterface $output)
    {
        $metadata = $this->entityManager->getClassMetadata($entityName);
        $type = $metadata->fieldMappings[$fieldName]['type'];

        // new value, by default what json gave us is the correct typed value
        $newTypedValue = $newJsonValue;

        // current value, by default the json, serialized value is the same as what's in the entity
        $getter = 'get'.ucfirst($fieldName);
        $currentJsonValue = $currentTypedValue = $entity->$getter();

        // if the field is a date, the default assumptions above are wrong
        if (in_array($type, ['date', 'datetime'])) {
            if ($newJsonValue !== null) {
                $newTypedValue = new DateTime($newJsonValue);
            }
            if ($currentTypedValue !== null) {
                switch ($type) {
                    case 'date':
                        /* @var DateTime $currentTypedValue*/
                        $currentJsonValue = $currentTypedValue->format('Y-m-d');
                        break;
                    case 'datetime':
                        /* @var DateTime $currentTypedValue*/
                        $currentJsonValue = $currentTypedValue->format('Y-m-d H:i:s');
                }
            }
        }

        $different = ($currentJsonValue !== $newJsonValue);
        if ($different) {
            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter($newTypedValue);
            $output->writeln(
                "Changing the <info>$fieldName</info> of <info>"
                . $entity
                . "</info> ($currentJsonValue => $newJsonValue)"
            );
        }
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $data
     * @param $key
     * @param OutputInterface $output
     * @param bool $isMandatory
     * @throws Exception
     */
    protected function copyKeyToEntity($entity, $entityName, $data, $key, OutputInterface $output, $isMandatory = true)
    {
        $metadata = $this->entityManager->getClassMetadata($entityName);

        if (!key_exists($key, $data)) {
            if ($isMandatory) {
                throw new Exception("Missing key [$key] in " . json_encode($data));
            } else {
                $data[$key] = null;
            }
        }
        $value = $data[$key];

        if (!key_exists($key, $metadata->fieldNames)) {
            throw new Exception("Missing column [$key] in entity ".$entityName);
        }
        $fieldName = $metadata->fieldNames[$key];

        $this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value, $output);
    }

    /**
     * @param $entityName
     * @param $data
     * @param $mandatoryKeys
     * @param $foreignKeys
     * @param $optionalKeys
     * @param OutputInterface $output
     * @return object|null
     * @throws Exception
     */
    protected function getEntityFromData(
        $entityName,
        $data,
        $mandatoryKeys,
        $foreignKeys,
        $optionalKeys,
        OutputInterface $output
    ) {
        if (!key_exists('code', $data)) {
            throw new Exception("Missing key [code] in ".json_encode($data));
        }

        $entity = $this->entityManager->getRepository($entityName)->findOneBy(['code' => $data['code']]);
        if (!$entity) {
            $entity = new $entityName();
        }
        $orig = $entity->serialize();

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, $output, true);
        }

        foreach ($optionalKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, $output, false);
        }

        foreach ($foreignKeys as $key) {
            $foreignEntityShortName = ucfirst(str_replace('_code', '', $key));

            if (!key_exists($key, $data)) {
                throw new Exception("Missing key [$key] in ".json_encode($data));
            }

            $foreignCode = $data[$key];
            if (!key_exists($foreignEntityShortName, $this->collections)) {
                throw new Exception("No collection for [$foreignEntityShortName] in ".json_encode($data));
            }
            if (!key_exists($foreignCode, $this->collections[$foreignEntityShortName])) {
                throw new Exception("Invalid code [$foreignCode] for key [$key] in ".json_encode($data));
            }
            $foreignEntity = $this->collections[$foreignEntityShortName][$foreignCode];

            $getter = 'get'.$foreignEntityShortName;
            if (!$entity->$getter() || $entity->$getter()->getId() !== $foreignEntity->getId()) {
                $setter = 'set'.$foreignEntityShortName;
                $entity->$setter($foreignEntity);
                $output->writeln("Changing the <info>$key</info> of <info>".$entity . "</info>");
            }
        }

        // special case for Card
        if ($entityName === Card::class) {
            // calling a function whose name depends on the type_code
            $functionName = 'import' . $entity->getType()->getName() . 'Data';
            $this->$functionName($entity, $data, $output);
        }

        if ($entity->serialize() !== $orig) {
            return $entity;
        }

        return null;
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importAgendaData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importAttachmentData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
            'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importCharacterData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
            'cost',
            'strength',
            'is_military',
            'is_intrigue',
            'is_power'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importEventData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
            'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importLocationData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
            'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importPlotData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [
            'claim',
            'income',
            'initiative',
            'reserve'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param CardInterface $card
     * @param $data
     * @param OutputInterface $output
     * @throws Exception
     */
    protected function importTitleData(CardInterface $card, $data, OutputInterface $output)
    {
        $mandatoryKeys = [];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, Card::class, $data, $key, $output, true);
        }
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws Exception
     */
    protected function getDataFromFile(SplFileInfo $fileinfo): array
    {
        $file = $fileinfo->openFile('r');
        $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $lines = [];
        foreach ($file as $line) {
            if ($line !== false) {
                $lines[] = $line;
            }
        }
        $content = implode('', $lines);

        $data = json_decode($content, true);

        if ($data === null) {
            throw new Exception(
                "File ["
                . $fileinfo->getPathname()
                . "] contains incorrect JSON (error code "
                . json_last_error()
                . ")"
            );
        }

        return $data;
    }

    /**
     * @param $path
     * @param $filename
     * @return SplFileInfo
     * @throws Exception
     */
    protected function getFileInfo($path, $filename): SplFileInfo
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $filepath = $path . DIRECTORY_SEPARATOR . $filename;

        if (!$fs->exists($filepath)) {
            throw new Exception("No $filename file found at [$path]");
        }

        return new SplFileInfo($filepath);
    }

    /**
     * @param $path
     * @return GlobIterator
     * @throws Exception
     */
    protected function getPacksDirectoryFilesIterator($path)
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $packsDirectoryPath = $path . DIRECTORY_SEPARATOR . self::PACKS_SUBDIRECTORY_NAME;

        if (!$fs->exists($packsDirectoryPath)) {
            throw new Exception("No '" . self::PACKS_SUBDIRECTORY_NAME . "' directory found at [$path]");
        }

        $iterator = new GlobIterator($packsDirectoryPath . DIRECTORY_SEPARATOR . '*.json');

        if (!$iterator->count()) {
            throw new Exception("No JSON files found in $packsDirectoryPath.");
        }

        return $iterator;
    }

    /**
     * @param string $entityName
     * @return array
     */
    protected function loadCollection($entityName)
    {
        $collection = [];
        $entities = $this->entityManager->getRepository($entityName)->findAll();
        foreach ($entities as $entity) {
            $collection[$entity->getCode()] = $entity;
        }
        return $collection;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return array
     * @throws Exception
     */
    protected function readCardsFromJsonFile(SplFileInfo $fileInfo)
    {
        $code = $fileInfo->getBasename('.json');

        $pack = $this->entityManager->getRepository(Pack::class)->findOneBy(['code' => $code]);
        if (!$pack) {
            throw new Exception("Unable to find Pack [$code]");
        }

        return $this->getDataFromFile($fileInfo);
    }

    /**
     * @param array $rawData
     * @return array
     */
    protected function extractCardNamesWithMultipleInstances(array $rawData): array
    {
        $names = [];
        $packs = array_values($rawData);
        foreach ($packs as $pack) {
            foreach ($pack['cards'] as $cardData) {
                $name = $cardData['name'];
                if (array_key_exists($name, $names)) {
                    $names[$name] = $names[$name] + 1;
                } else {
                    $names[$name] = 1;
                }
            }
        }

        return array_keys(array_filter($names, function ($value) {
            return ($value > 1);
        }));
    }

    /**
     * Flips the grouping of packs by cycles on its ear.
     * Returns each pack mapped to its cycle and its position within the cycle.
     * @param array $cycles
     * @return array
     */
    protected function mapPacksToCycles(array $cycles): array
    {
        $rhett = [];
        foreach ($cycles as $cycle) {
            $cycleCode = $cycle['id'];
            $packs = $cycle['packs'];
            $position = 1;
            foreach ($packs as $pack) {
                $rhett[$pack] = ['cycle' => $cycleCode, 'position' => $position];
                $position++;
            }
        }
        return $rhett;
    }

    /**
     * Adds packs to cycles in their proper order.
     * @param array $rawCyclesData
     * @param array $rawPackData
     * @return array
     */
    protected function groupPacksInCycles(array $rawCyclesData, array $rawPackData): array
    {
        $cycles = [];
        foreach ($rawCyclesData as $rawCycle) {
            $cycle = [];
            foreach ($rawCycle['packs'] as $packCode) {
                $pack = $rawPackData[$packCode];
                $cycle[] = $pack;
            }
            $cycles[]  = $cycle;
        }
        return $cycles;
    }

    /**
     * @param $value
     * @return mixed
     */
    protected function convertXValue($value)
    {
        if (in_array((string) $value, ['X', '-'])) {
            return null;
        }
        return $value;
    }
}
