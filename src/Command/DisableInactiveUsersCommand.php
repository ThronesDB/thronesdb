<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use App\Entity\User;
use App\Entity\UserInterface;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:disable-inactive-users', description: 'Disables user accounts without any activity.')]
class DisableInactiveUsersCommand extends Command
{
    protected const GRACE_PERIOD = 'P7D';

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new DateTime();
        $xDaysAgo = $now->sub(new DateInterval(self::GRACE_PERIOD));

        $users = $this->entityManager->getRepository(User::class)->getInactiveUsers($xDaysAgo);
        /** @var UserInterface $user */
        foreach ($users as $user) {
            $user->setEnabled(false);
        }
        $this->entityManager->flush();
        $output->writeln(count($users) . ' inactive user accounts were disabled.');
        return Command::SUCCESS;
    }
}
