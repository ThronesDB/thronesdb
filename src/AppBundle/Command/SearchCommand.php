<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class SearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('app:search')
        ->setDescription('Search cards')
        ->addArgument(
                'query',
                \Symfony\Component\Console\Input\InputArgument::REQUIRED,
                "Search query, eg e:core"
        )
        ->addOption(
                'output',
                'o',
                \Symfony\Component\Console\Input\InputOption::VALUE_REQUIRED,
                "Properties of each card to output (comma-separated list)",
                'name'
        )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument('query');
        $fields = explode(',', $input->getOption('output'));
        
        $service = $this->getContainer()->get('cards_data');
        
        $conditions = $service->syntax($query);
        
        $conditions = $service->validateConditions($conditions);

        $q = $service->buildQueryFromConditions($conditions);
        
        $result = [];
        
        $rows = $service->get_search_rows($conditions, 'set');
        foreach ($rows as $card) {
            $cardinfo = $service->getCardInfo($card, false, null);
            $filtered = array_filter($cardinfo, function ($key) use ($fields) {
                return in_array($key, $fields);
            }, ARRAY_FILTER_USE_KEY);
            $result[] = $filtered;
        }

        $table = new \Symfony\Component\Console\Helper\Table($output);
        $table->setRows($result);
        $table->render();
        
        $output->writeln('');
        $output->writeln(count($rows). " cards");
    }
}
