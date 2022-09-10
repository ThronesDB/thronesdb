<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Restriction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Restrictions data importer.
 *
 * @package App\Command
 * * @todo provide more detailed output as to what was newly imported and updated. [ST 2020/12/07]
 */
class ImportRestrictionsCommand extends Command
{
    protected const RESTRICTIONS_FILE_NAME = 'restricted-list.json';

    protected const FORMAT_JOUST = 'joust';

    protected const FORMAT_MELEE = 'melee';

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:restrictions:import')
            ->setDescription(
                'Import deck-building restrictions from a local thronteki-json-data repository.'
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
        $data = $this->loadDataFromFile($path);
        $existingRestrictions = $this->loadExistingRestrictions();

        if (empty($data)) {
            $output->writeln('No restrictions to import found.');
            return 0;
        }

        foreach ($data as $item) {
            $code = $item['code'];
            $issuer = $item['issuer'];
            $cardSet = $item['cardSet'];
            $title = $item['name'];
            $effectiveOn = new DateTime($item['date']);
            $version = $item['version'];
            $contents = $this->buildContents($item);

            if (array_key_exists($code, $existingRestrictions)) {
                $output->writeln("Updating existing restrictions (code = ${code}).");
                $restriction = $existingRestrictions[$code];
            } else {
                $output->writeln("Importing new restrictions (code = ${code}).");
                $restriction = new Restriction();
                $restriction->setCode($code);
                $restriction->setActive(false);
            }
            $restriction->setTitle($title);
            $restriction->setIssuer($issuer);
            $restriction->setEffectiveOn($effectiveOn);
            $restriction->setCardSet($cardSet);
            $restriction->setVersion($version);
            $restriction->setContents($contents);

            $this->entityManager->persist($restriction);
        }

        $this->entityManager->flush();
        return 0;
    }

    /**
     * @param string $path
     * @return array
     * @throws Exception
     */
    protected function loadDataFromFile(string $path): array
    {
        $fs = new Filesystem();

        if (!$fs->exists($path)) {
            throw new Exception("No repository found at [$path]");
        }

        $filepath = $path . DIRECTORY_SEPARATOR . self::RESTRICTIONS_FILE_NAME;

        if (! $fs->exists($filepath)) {
            throw new Exception('No ' . self::RESTRICTIONS_FILE_NAME . " file found at [${path}].");
        }

        $text = file_get_contents($filepath);
        $data = json_decode($text, true);

        if ($data === null) {
            throw new Exception(
                "File [${$filepath}] contains incorrect JSON (error code " . json_last_error() . ".)"
            );
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function loadExistingRestrictions(): array
    {
        $entities = $this->entityManager->getRepository(Restriction::class)->findAll();
        $map = [];
        foreach ($entities as $entity) {
            $map[$entity->getCode()] = $entity;
        }
        return $map;
    }

    /**
     * @param array $data
     * @return array
     */
    protected function buildContents(array $data): array
    {
        $rhett = [
            'joust' => [
                'url' => null,
                'banned' => [],
                'restricted' => [],
                'restricted_pods' => [],
            ],
            'melee' => [
                'url' => null,
                'banned' => [],
                'restricted' => [],
                'restricted_pods' => [],
            ],
        ];

        $bannedInAllFormats = $data['bannedCards'];

        foreach ($data['formats'] as $format) {
            $name = $format['name'];
            $rhett[$name]['url'] = array_key_exists('url', $format) ? $format['url'] : null;
            $restricted = $format['restricted'];
            sort($restricted);
            $rhett[$name]['restricted'] = $restricted;
            $banned = $bannedInAllFormats;
            if (array_key_exists('banned', $format)) {
                 $banned = array_merge($banned, $format['banned']);
            }
            sort($banned);
            $rhett[$name]['banned'] = $banned;
            if (array_key_exists('pods', $format)) {
                $rhett[$name]['restricted_pods'] = $format['pods'];
                for ($i = 0, $n = count($rhett[$name]['restricted_pods']); $i < $n; $i++) {
                    $rhett[$name]['restricted_pods'][$i]['title']
                        = (self::FORMAT_JOUST === $name ? 'P' : 'MP') . ($i + 1);
                }
            }
        }

        return $rhett;
    }
}
