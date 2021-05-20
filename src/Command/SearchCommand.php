<?php

namespace App\Command;

use App\Services\CardsData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
class SearchCommand extends Command
{
    protected CardsData $service;

    public function __construct(CardsData $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    protected function configure()
    {
        $this
        ->setName('app:search')
        ->setDescription('Search cards.')
        ->addArgument(
            'query',
            InputArgument::REQUIRED,
            "Search query, eg e:core"
        )
        ->addOption(
            'output',
            'o',
            InputOption::VALUE_REQUIRED,
            "Properties of each card to output (comma-separated list)",
            'name'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = $input->getArgument('query');
        $fields = explode(',', $input->getOption('output'));

        $conditions = $this->service->syntax($query);

        $conditions = $this->service->validateConditions($conditions);

        $result = [];

        $rows = $this->service->getSearchRows($conditions, 'set');
        foreach ($rows as $card) {
            $cardinfo = $this->service->getCardInfo($card, false, null);
            $filtered = array_filter($cardinfo, function ($key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);
            $result[] = $filtered;
        }

        $table = new Table($output);
        $table->setRows($result);
        $table->render();

        $output->writeln('');
        $output->writeln(count($rows) . " cards");

        return 0;
    }
}
