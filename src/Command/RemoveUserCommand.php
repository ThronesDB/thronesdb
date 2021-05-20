<?php

namespace App\Command;

use App\Entity\Deck;
use App\Entity\Decklist;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
class RemoveUserCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:user:remove')
            ->setDescription('Deactivates one user and delete all its content.')
            ->addArgument(
                'user_id',
                InputArgument::REQUIRED,
                'Id of the user'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user_id = $input->getArgument('user_id');
        $user = $this->entityManager->getRepository(User::class)->find($user_id);

        if (! $user) {
            $output->writeln("User not found.");
            return 0;
        }

        $output->writeln("User " . $user->getUsername());

        $decks = $this->entityManager->getRepository(Deck::class)->findBy([ 'user' => $user ]);

        $output->writeln('Deleting' . count($decks) . ' decks.');

        foreach ($decks as $deck) {
            $children = $this->entityManager->getRepository(Decklist::class)->findBy([ 'parent' => $deck ]);
            foreach ($children as $child) {
                $child->setParent(null);
            }
            $this->entityManager->remove($deck);
        }

        $output->writeln("Decks deleted.");

        $decklists = $this->entityManager->getRepository(Decklist::class)->findBy([ 'user' => $user ]);

        $output->writeln('Deleting ' . count($decklists) . ' decklists.');

        foreach ($decklists as $decklist) {
            $successors = $this->entityManager->getRepository(Decklist::class)->findBy([
                'precedent' => $decklist
            ]);
            foreach ($successors as $successor) {
                $successor->setPrecedent(null);
            }

            $children = $this->entityManager->getRepository(Deck::class)->findBy([ 'parent' => $decklist ]);
            foreach ($children as $child) {
                $child->setParent(null);
            }

            $this->entityManager->remove($decklist);
        }

        $output->writeln("Decklists deleted.");

        $user->setEnabled(false);

        $output->writeln("User deactivated.");

        $this->entityManager->flush();

        return 0;
    }
}
