<?php

namespace App\Command;

use App\Entity\Card;
use App\Entity\CardInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CheckMultipleCommand
 * @package App\Command
 */
class CheckMultipleCommand extends ContainerAwareCommand
{
    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct('app:check:multiple');
    }

    protected function configure()
    {
        $this
            ->setName('app:check:multiple')
            ->setDescription('Check card is_multiple field');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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
    }
}
