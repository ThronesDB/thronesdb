<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
#[AsCommand(name: 'app:delete-inactive-users', description: 'Delete inactive users that were created 48 hours or longer ago.')]
class DeleteInactiveCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limit = new DateTime();
        $limit->sub(new DateInterval('PT48H'));
        $count = 0;

        $users = $this->entityManager->getRepository(User::class)->findBy(array('enabled' => false));
        foreach ($users as $user) {
            /* @var UserInterface $user */
            if ($user->getDateCreation() < $limit) {
                $count++;
                $this->entityManager->remove($user);
            }
        }
        $this->entityManager->flush();
        $output->writeln(date('c') . " Delete $count inactive users.");
        return Command::SUCCESS;
    }
}
