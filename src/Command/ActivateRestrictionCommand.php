<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Restriction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Activates restrictions.
 *
 * @package App\Command
 */
class ActivateRestrictionCommand extends Command
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
            ->setName('app:restrictions:activate')
            ->setDescription(
                'Activates restrictions.'
            );
    }
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(Restriction::class);

        $restrictions = $repo->findBy(['active' => false], ['effectiveOn' => 'desc']);

        $options = [];
        $map = [];

        foreach ($restrictions as $restriction) {
            $map[$restriction->getCode()] = $restriction;
            $options[$restriction->getCode()] = $restriction->getIssuer()
                . ' (Version: ' . $restriction->getVersion()  . ')'
                . ', effective on ' . $restriction->getEffectiveOn()->format('Y-m-d');
        }

        if (empty($options)) {
            $output->writeln('No restrictions to activate found.');
        } else {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select which restrictions to activate: ',
                $options
            );
            $question->setMultiselect(true);
            $choices = $helper->ask($input, $output, $question);

            foreach ($choices as $choice) {
                $restriction = $map[$choice];
                $restriction->setActive(true);
                $this->entityManager->persist($restriction);
            }
            $this->entityManager->flush();

            if (1 === count($choices)) {
                $output->writeln("${choices[0]} has been activated.");
            } else {
                $output->writeln('The following restrictions have been activated: ' . implode(', ', $choices) . '.');
            }
        }

        return 0;
    }
}
