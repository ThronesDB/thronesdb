<?php
declare(strict_types=1);

namespace App\Command;

use App\Entity\Restriction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * List information about available restrictions.
 *
 * @package App\Command
 */
class ListRestrictionsCommand extends Command
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
            ->setName('app:restrictions:list')
            ->setDescription(
                'List available restrictions.'
            );
    }
    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(Restriction::class);

        $restrictions = $repo->findBy([], ['active' => 'desc', 'effectiveOn' => 'desc']);

        if (empty($restrictions)) {
            $output->writeln('No restrictions found.');
        } else {
            $table = new Table($output);
            $table
                ->setHeaders(['Code', 'Issuer', 'Version', 'Active', 'Date effective']);

            foreach ($restrictions as $restriction) {
                $table->addRow([
                    $restriction->getCode(),
                    $restriction->getIssuer(),
                    $restriction->getVersion(),
                    $restriction->isActive() ? '<fg=green>yes</>' : '<fg=red>no</>',
                    $restriction->getEffectiveOn()->format('Y-m-d')
                ]);
            }
            $table->render();
        }

        return 0;
    }
}
