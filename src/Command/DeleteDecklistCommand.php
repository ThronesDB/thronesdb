<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use App\Entity\Deck;
use App\Entity\Decklist;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
#[AsCommand(name: 'app:decklist:delete', description: 'Delete one decklist')]
class DeleteDecklistCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
        ->addArgument(
            'decklist_id',
            InputArgument::REQUIRED,
            'Id of the decklist'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $decklist_id = $input->getArgument('decklist_id');
        $decklist = $this->entityManager->getRepository(Decklist::class)->find($decklist_id);

        $successors = $this->entityManager->getRepository(Decklist::class)->findBy(array(
                'precedent' => $decklist
        ));
        foreach ($successors as $successor) {
            /* @var DecklistInterface $successor */
            $successor->setPrecedent(null);
        }

        $children = $this->entityManager->getRepository(Deck::class)->findBy(array(
                'parent' => $decklist
        ));
        foreach ($children as $child) {
            /* @var DeckInterface $child */
            $child->setParent(null);
        }

        $this->entityManager->flush();
        $this->entityManager->remove($decklist);
        $this->entityManager->flush();

        $output->writeln("Decklist deleted");

        return Command::SUCCESS;
    }
}
