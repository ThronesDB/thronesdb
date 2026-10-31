<?php

declare(strict_types=1);

namespace App\Command\BannerAlert;

use Symfony\Component\Console\Attribute\AsCommand;
use App\Entity\BannerAlert;
use App\Entity\BannerAlertInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists all banner alerts.
 *
 * @package App\Command
 */
#[AsCommand(name: 'app:banner-alerts:list', description: 'List available banner alerts.')]
class ListCommand extends Command
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(BannerAlert::class);

        $alerts = $repo->findBy([], ['id' => 'desc']);

        if (empty($alerts)) {
            $output->writeln('No banner alerts found.');
        } else {
            $table = new Table($output);
            $table
                ->setHeaders(['Id', 'Name', 'Description', 'Active', 'Level', 'Date created']);

            foreach ($alerts as $alert) {
                $table->addRow([
                    'a' . $alert->getId(),
                    $alert->getName(),
                    $alert->getDescription(),
                    $alert->isActive() ? '<fg=green>yes</>' : '<fg=red>no</>',
                    BannerAlertInterface::LEVEL_INFO === $alert->getLevel()
                        ? '<fg=blue>info</>'
                        : '<fg=yellow>warning</>' ,
                    $alert->getDateCreation()->format('Y-m-d H:i:s')
                ]);
            }
            $table->render();
        }

        return Command::SUCCESS;
    }
}
