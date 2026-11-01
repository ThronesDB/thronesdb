<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use App\Entity\Card;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
#[AsCommand(name: 'app:check:multiple', description: 'Check card is_multiple field')]
class CheckMultipleCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cards = $this->entityManager->getRepository(Card::class)->findAll();

        $count = [];
        /* @var CardInterface $card */
        foreach ($cards as $card) {
            $count[$card->getName()] = ($count[$card->getName()] ?? 0) + 1;
        }

        foreach ($cards as $card) {
            if ($count[$card->getName()] === 1 && $card->getIsMultiple() === true) {
                $output->writeln(
                    sprintf(
                        '<error>Card %s %s must not be multiple.</error>',
                        $card->getCode(),
                        $card->getName()
                    )
                );
            }
            if ($count[$card->getName()] > 1 && $card->getIsMultiple() === false) {
                $output->writeln(
                    sprintf(
                        '<error>Card %s %s must be multiple.</error>',
                        $card->getCode(),
                        $card->getName()
                    )
                );
            }
        }
        return Command::SUCCESS;
    }
}
