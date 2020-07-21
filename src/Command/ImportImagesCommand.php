<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardInterface;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ImportImagesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:import:images')
            ->setDescription('Download missing card images from FFG websites');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client([
            'http_errors' => false,
        ]);

        /* @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var CardInterface[] $cards */
        $cards = $em->getRepository(Card::class)->findBy(['imageUrl' => null]);

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

        $em->flush();
    }
}
