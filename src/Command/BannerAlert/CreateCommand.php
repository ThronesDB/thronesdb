<?php

declare(strict_types=1);

namespace App\Command\BannerAlert;

use App\Entity\BannerAlert;
use App\Entity\BannerAlertInterface;
use App\Entity\Restriction;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Creates a new banner alert.
 *
 * @package App\Command
 */
class CreateCommand extends Command
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
            ->setName('app:banner-alerts:create')
            ->setDescription(
                'Creates a banner alert.'
            )
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'The name of the alert. Will not be displayed to the user.'
            )
            ->addArgument(
                'description',
                InputArgument::REQUIRED,
                'The alert contents. Will be displayed to the user.',
            )
            ->addOption(
                'activate',
                null,
                InputOption::VALUE_NONE,
                'Activate this alert on creation? (default is no.)',
            )
            ->addOption(
                'is-warning',
                null,
                InputOption::VALUE_NONE,
                'Is this alert at warning level? (default is no.)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $alert = new BannerAlert();
        $alert->setName($input->getArgument('name'));
        $alert->setDescription($input->getArgument('description'));
        $alert->setActive($input->getOption('activate'));
        $alert->setLevel(
            $input->getOption('is-warning') ? BannerAlertInterface::LEVEL_WARNING : BannerAlertInterface::LEVEL_INFO
        );
        $this->entityManager->persist($alert);
        $this->entityManager->flush();
        return 0;
    }
}
