<?php

declare(strict_types=1);

namespace App\Command\BannerAlert;

use App\Entity\BannerAlert;
use App\Entity\BannerAlertInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Deletes banner alerts.
 *
 * @package App\Command
 */
class DeleteCommand extends Command
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
            ->setName('app:banner-alerts:delete')
            ->setDescription(
                'Deletes banner alerts.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(BannerAlert::class);

        $alerts = $repo->findBy([], ['id' => 'desc']);

        $options = [];
        $map = [];

        /** @var BannerAlertInterface $alert */
        foreach ($alerts as $alert) {
            $map['a' . $alert->getId()] = $alert;
            $options['a' . $alert->getId()] =
                "{$alert->getName()} (Created on: {$alert->getDateCreation()->format('Y-m-d H:i:s')})";
        }

        if (empty($options)) {
            $output->writeln('No banner alerts to delete found.');
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select which banner alerts to delete: ',
                $options
            );
            $question->setMultiselect(true);
            $choices = $helper->ask($input, $output, $question);

            foreach ($choices as $choice) {
                $alert = $map[$choice];
                $this->entityManager->remove($alert);
            }
            $this->entityManager->flush();

            if (1 === count($choices)) {
                $output->writeln("${choices[0]} has been deleted.");
            } else {
                $output->writeln('The following banner alerts have been deleted: ' . implode(', ', $choices) . '.');
            }
        }

        return 0;
    }
}
