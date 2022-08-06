<?php

namespace App\Command;

use App\Entity\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

class CreateOauthClientCommand extends Command
{
    private $clientManager;

    public function __construct(ClientManagerInterface $clientManager)
    {
        parent::__construct();

        $this->clientManager = $clientManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('app:oauth2:create-client')
            ->setDescription('Creates a new OAuth2 client')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                "The client application's name",
            )
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                null
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types..',
                null
            )
            ->setHelp(<<<EOT
The <info>%command.name%</info> command creates a new client.

<info>php %command.full_name% name [--redirect-uri=...] [--grant-type=...]</info>

EOT
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Client Information');

        /* @var Client $client*/
        $client = $this->clientManager->createClient();
        $client->setName($input->getArgument('name'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));

        // Save the client
        $this->clientManager->updateClient($client);

        // Give the credentials back to the user
        $headers = ['Name', 'Client ID', 'Client Secret'];
        $rows = [
            [$client->getName(), $client->getPublicId(), $client->getSecret()],
        ];

        $io->table($headers, $rows);

        return 0;
    }
}
