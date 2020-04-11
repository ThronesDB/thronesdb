<?php

namespace AppBundle\Command;

use DateTime;
use Doctrine\ORM\ORMException;
use Exception;
use GlobIterator;
use SplFileInfo;
use SplFileObject;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Card;

/**
 * Data import command.
 * Class ImportStdCommand
 * @package AppBundle\Command
 */
class ImportStdCommand extends ContainerAwareCommand
{
    /* @var $em EntityManager */
    private $em;

    /* @var $output OutputInterface */
    private $output;

    /**
     * @var array
     */
    private $collections = [];

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
        ->setName('app:import:std')
        ->setDescription(
            'Import cards data file in json format from a copy of https://github.com/ThronesDB/thronesdb-json-data'
        )
        ->addArgument(
            'path',
            InputArgument::REQUIRED,
            'Path to the repository'
        );
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getArgument('path');
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->output = $output;

        /* @var $helper QuestionHelper */
        $helper = $this->getHelper('question');

        // factions

        $output->writeln("Importing Factions...");
        $factionsFileInfo = $this->getFileInfo($path, 'factions.json');
        $imported = $this->importFactionsJsonFile($factionsFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Faction');
        $output->writeln("Done.");

        // types

        $output->writeln("Importing Types...");
        $typesFileInfo = $this->getFileInfo($path, 'types.json');
        $imported = $this->importTypesJsonFile($typesFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Type');
        $output->writeln("Done.");

        // cycles

        $output->writeln("Importing Cycles...");
        $cyclesFileInfo = $this->getFileInfo($path, 'cycles.json');
        $imported = $this->importCyclesJsonFile($cyclesFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Cycle');
        $output->writeln("Done.");

        // second, packs

        $output->writeln("Importing Packs...");
        $packsFileInfo = $this->getFileInfo($path, 'packs.json');
        $imported = $this->importPacksJsonFile($packsFileInfo);
        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $this->loadCollection('Pack');
        $output->writeln("Done.");

        // third, cards

        $output->writeln("Importing Cards...");
        $fileSystemIterator = $this->getFileSystemIterator($path);
        $rawData = [];
        foreach ($fileSystemIterator as $fileinfo) {
            $baseName = $fileinfo->getBasename('.json');
            $rawData[$baseName] = $this->readCardsFromJsonFile($fileinfo);
        }

        $multiNames = $this->extractCardNamesWithMultipleInstances($rawData);

        $imported = [];
        foreach ($rawData as $cardsData) {
            $imported = array_merge($imported, $this->importCards($cardsData, $multiNames));
        }

        if (count($imported)) {
            $question = new ConfirmationQuestion("Do you confirm? (Y/n) ", true);
            if (!$helper->ask($input, $output, $question)) {
                die();
            }
        }
        $this->em->flush();
        $output->writeln("Done.");
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importFactionsJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach ($list as $data) {
            $faction = $this->getEntityFromData('AppBundle\\Entity\\Faction', $data, [
                    'code',
                    'name',
                    'is_primary'
            ], [], []);
            if ($faction) {
                $result[] = $faction;
                $this->em->persist($faction);
            }
        }

        return $result;
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importTypesJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];

        $list = $this->getDataFromFile($fileinfo);
        foreach ($list as $data) {
            $type = $this->getEntityFromData('AppBundle\\Entity\\Type', $data, [
                    'code',
                    'name'
            ], [], []);
            if ($type) {
                $result[] = $type;
                $this->em->persist($type);
            }
        }

        return $result;
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importCyclesJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];
        $position = 0;
        $cyclesData = $this->getDataFromFile($fileinfo);
        foreach ($cyclesData as $cycleData) {
            $cycleData['position'] = $position;
            $cycle = $this->getEntityFromData('AppBundle\Entity\Cycle', $cycleData, [
                    'code',
                    'name',
                    'position',
                    'size'
            ], [], []);
            if ($cycle) {
                $result[] = $cycle;
                $this->em->persist($cycle);
            }
            $position++;
        }

        return $result;
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importPacksJsonFile(SplFileInfo $fileinfo)
    {
        $result = [];

        $position = [];

        $packsData = $this->getDataFromFile($fileinfo);
        foreach ($packsData as $packData) {
            $cycleCode = $packData['cycle_code'];
            if (array_key_exists($cycleCode, $position)) {
                $position[$cycleCode] = $position[$cycleCode] + 1;
            } else {
                $position[$cycleCode] = 1;
            }
            $packData['position'] = $position[$cycleCode];
            $pack = $this->getEntityFromData('AppBundle\Entity\Pack', $packData, [
                    'code',
                    'name',
                    'position',
                    'size',
                    'date_release',
                    'cgdb_id'
            ], [
                    'cycle_code'
            ], []);
            if ($pack) {
                $result[] = $pack;
                $this->em->persist($pack);
            }
        }

        return $result;
    }

    /**
     * @param array $cardsData
     * @param array $multiNames
     * @return array
     * @throws ORMException
     * @throws Exception
     */
    protected function importCards(array $cardsData, array $multiNames)
    {
        $result = [];
        foreach ($cardsData as $cardData) {
            $cardData['is_multiple'] = in_array($cardData['name'], $multiNames);
            $card = $this->getEntityFromData('AppBundle\Entity\Card', $cardData, [
                    'code',
                    'deck_limit',
                    'position',
                    'quantity',
                    'name',
                    'text',
                    'flavor',
                    'is_loyal',
                    'is_unique',
                    'is_multiple'
            ], [
                    'faction_code',
                    'pack_code',
                    'type_code'
            ], [
                    'designer',
                    'illustrator',
                    'traits',
                    'cost',
                    'octgn_id'
            ]);
            if ($card) {
                $result[] = $card;
                $this->em->persist($card);
            }
        }

        return $result;
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $fieldName
     * @param $newJsonValue
     * @throws Exception
     */
    protected function copyFieldValueToEntity($entity, $entityName, $fieldName, $newJsonValue)
    {
        $metadata = $this->em->getClassMetadata($entityName);
        $type = $metadata->fieldMappings[$fieldName]['type'];

        // new value, by default what json gave us is the correct typed value
        $newTypedValue = $newJsonValue;

        // current value, by default the json, serialized value is the same as what's in the entity
        $getter = 'get'.ucfirst($fieldName);
        $currentJsonValue = $currentTypedValue = $entity->$getter();

        // if the field is a data, the default assumptions above are wrong
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
            $this->output->writeln(
                "Changing the <info>$fieldName</info> of <info>"
                . $entity
                . "</info> ($currentJsonValue => $newJsonValue)"
            );
            $setter = 'set'.ucfirst($fieldName);
            $entity->$setter($newTypedValue);
        }
    }

    /**
     * @param $entity
     * @param $entityName
     * @param $data
     * @param $key
     * @param bool $isMandatory
     * @throws Exception
     */
    protected function copyKeyToEntity($entity, $entityName, $data, $key, $isMandatory = true)
    {
        $metadata = $this->em->getClassMetadata($entityName);

        if (!key_exists($key, $data)) {
            if ($isMandatory) {
                throw new Exception("Missing key [$key] in ".json_encode($data));
            } else {
                $data[$key] = null;
            }
        }
        $value = $data[$key];

        if (!key_exists($key, $metadata->fieldNames)) {
            throw new Exception("Missing column [$key] in entity ".$entityName);
        }
        $fieldName = $metadata->fieldNames[$key];

        $this->copyFieldValueToEntity($entity, $entityName, $fieldName, $value);
    }

    /**
     * @param $entityName
     * @param $data
     * @param $mandatoryKeys
     * @param $foreignKeys
     * @param $optionalKeys
     * @return object|null
     * @throws Exception
     */
    protected function getEntityFromData($entityName, $data, $mandatoryKeys, $foreignKeys, $optionalKeys)
    {
        if (!key_exists('code', $data)) {
            throw new Exception("Missing key [code] in ".json_encode($data));
        }

        $entity = $this->em->getRepository($entityName)->findOneBy(['code' => $data['code']]);
        if (!$entity) {
            $entity = new $entityName();
        }
        $orig = $entity->serialize();

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, true);
        }

        foreach ($optionalKeys as $key) {
            $this->copyKeyToEntity($entity, $entityName, $data, $key, false);
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
                $this->output->writeln("Changing the <info>$key</info> of <info>". $entity ."</info>");
                $setter = 'set'.$foreignEntityShortName;
                $entity->$setter($foreignEntity);
            }
        }

        // special case for Card
        if ($entityName === 'AppBundle\Entity\Card') {
            // calling a function whose name depends on the type_code
            $functionName = 'import' . $entity->getType()->getName() . 'Data';
            $this->$functionName($entity, $data);
        }

        if ($entity->serialize() !== $orig) {
            return $entity;
        }

        return null;
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importAgendaData(Card $card, $data)
    {
        $mandatoryKeys = [
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importAttachmentData(Card $card, $data)
    {
        $mandatoryKeys = [
                'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importCharacterData(Card $card, $data)
    {
        $mandatoryKeys = [
                'cost',
                'strength',
                'is_military',
                'is_intrigue',
                'is_power'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importEventData(Card $card, $data)
    {
        $mandatoryKeys = [
                'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importLocationData(Card $card, $data)
    {
        $mandatoryKeys = [
                'cost'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importPlotData(Card $card, $data)
    {
        $mandatoryKeys = [
                'claim',
                'income',
                'initiative',
                'reserve'
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param Card $card
     * @param $data
     * @throws Exception
     */
    protected function importTitleData(Card $card, $data)
    {
        $mandatoryKeys = [
        ];

        foreach ($mandatoryKeys as $key) {
            $this->copyKeyToEntity($card, 'AppBundle\Entity\Card', $data, $key, true);
        }
    }

    /**
     * @param SplFileInfo $fileinfo
     * @return mixed
     * @throws Exception
     */
    protected function getDataFromFile(SplFileInfo $fileinfo)
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
    protected function getFileInfo($path, $filename)
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $filepath = "$path/$filename";

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
    protected function getFileSystemIterator($path)
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $directory = 'pack';

        if (!$fs->exists("$path/$directory")) {
            throw new Exception("No '$directory' directory found at [$path]");
        }

        $iterator = new GlobIterator("$path/$directory/*.json");

        if (!$iterator->count()) {
            throw new Exception("No json file found at [$path/set]");
        }

        return $iterator;
    }

    /**
     * @param $entityShortName
     */
    protected function loadCollection($entityShortName)
    {
        $this->collections[$entityShortName] = [];

        $entities = $this->em->getRepository('AppBundle:'.$entityShortName)->findAll();

        foreach ($entities as $entity) {
            $this->collections[$entityShortName][$entity->getCode()] = $entity;
        }
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return array
     * @throws Exception
     */
    protected function readCardsFromJsonFile(SplFileInfo $fileInfo)
    {
        $code = $fileInfo->getBasename('.json');

        $pack = $this->em->getRepository('AppBundle:Pack')->findOneBy(['code' => $code]);
        if (!$pack) {
            throw new Exception("Unable to find Pack [$code]");
        }

        return $this->getDataFromFile($fileInfo);
    }

    /**
     * @param array $rawData
     * @return array
     */
    protected function extractCardNamesWithMultipleInstances(array $rawData)
    {
        $names = [];
        foreach ($rawData as $cardsData) {
            foreach ($cardsData as $cardData) {
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
}
