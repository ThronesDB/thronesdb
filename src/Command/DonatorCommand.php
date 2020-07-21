<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class DonatorCommand extends ContainerAwareCommand
{
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $donation = $input->getArgument('donation');

        /* @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository(User::class);
        /* @var UserInterface $user */
        $user = $repo->findOneBy(array('email' => $email));
        if (!$user) {
            $user = $repo->findOneBy(array('username' => $email));
        }

        if ($user) {
            if ($donation) {
                $user->setDonation($donation + $user->getDonation());
                $em->flush();
                $output->writeln(date('c') . " " . "Success");
            } else {
                $output->writeln(date('c') . " User " . $user->getUsername() . " donated " . $user->getDonation());
            }
        } else {
            $output->writeln(date('c') . " " . "Cannot find user [$email]");
        }
    }
}
