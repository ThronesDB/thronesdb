<?php

namespace App\Command;

use FOS\OAuthServerBundle\Model\ClientManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package App\Command
 */
class CreateClientCommand extends Command
{
    protected ClientManagerInterface $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();
        $this->clientManager = $clientManager;
    }

    protected function configure()
    {
        $this
            ->setName('app:oauth-server:client:create')
            ->setDescription('Creates a new client')
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types..',
                ["authorization_code", "refresh_token"]
            )
            ->addArgument(
                'redirect-uri',
                InputArgument::REQUIRED,
                'Sets redirect uri for client'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $redirectUris = [ $input->getArgument('redirect-uri') ];

        $client = $this->clientManager->createClient();
        $client->setRedirectUris($redirectUris);
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $this->clientManager->updateClient($client);
        $output->writeln(
            sprintf(
                'Added a new client with public id <info>%s</info>, secret <info>%s</info>',
                $client->getPublicId(),
                $client->getSecret()
            )
        );
        return 0;
    }
}
