<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportImagesCommand extends Command
{
    protected EntityManagerInterface  $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:import:images')
            ->setDescription('Download missing card images from FFG websites');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = new Client([ 'http_errors' => false ]);

        /** @var CardInterface[] $cards */
        $cards = $this->entityManager->getRepository(Card::class)->findBy(['imageUrl' => null]);

        foreach ($cards as $card) {
            $position = $card->getPosition();
            $cgdbId = $card->getPack()->getCgdbId();

            if (empty($cgdbId)) {
                $output->writeln(sprintf('Skip %s because its cgdb_id is not defined', $card->getPack()->getName()));
                continue;
            }

            if ($cgdbId === 1 && $position >= 198 && $position <= 205) {
                $position = $position . 'B';
            }

            $url = sprintf('http://lcg-cdn.fantasyflightgames.com/got2nd/GT%02d_%s.jpg', $cgdbId, $position);

            $response = $client->request('GET', $url);

            if ($response->getStatusCode() === 200) {
                $card->setImageUrl($url);
                $output->writeln(sprintf('Found image for %s %s at url %s', $card->getCode(), $card->getName(), $url));
            } else {
                $output->writeln(
                    sprintf(
                        '<error>Image missing for %s %s at url %s</error>',
                        $card->getCode(),
                        $card->getName(),
                        $url
                    )
                );
            }
        }

        $this->entityManager->flush();
        return 0;
    }
}
