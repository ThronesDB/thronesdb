<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
class DonatorCommand extends Command
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
        ->setName('app:donator')
        ->setDescription('Add a donation to a user by email address or username')
        ->addArgument(
            'email',
            InputArgument::REQUIRED,
            'Email address or username of user'
        )
        ->addArgument(
            'donation',
            InputArgument::OPTIONAL,
            'Amount of donation'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $donation = $input->getArgument('donation');

        $repo = $this->entityManager->getRepository(User::class);
        $user = $repo->findOneBy(array('email' => $email));
        if (!$user) {
            $user = $repo->findOneBy(array('username' => $email));
        }

        if ($user) {
            if ($donation) {
                $user->setDonation($donation + $user->getDonation());
                $this->entityManager->flush();
                $output->writeln(date('c') . " " . "Success");
            } else {
                $output->writeln(date('c') . " User " . $user->getUsername() . " donated " . $user->getDonation());
            }
        } else {
            $output->writeln(date('c') . " " . "Cannot find user [$email]");
        }
        return 0;
    }
}
