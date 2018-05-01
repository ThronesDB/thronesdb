<?php

declare(strict_types=1);

namespace AppBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Environment\InitializedContextEnvironment;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\Bundle\DoctrineBundle\Registry;

class DoctrineContext implements Context
{
    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var ApiContext
     */
    private $apiContext;

    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();

        if ($environment instanceof InitializedContextEnvironment) {
            $this->apiContext = $environment->getContext(ApiContext::class);
        }
    }

    /**
     * @When /^I load Doctrine data from "(?P<tableName>.*)"$/
     * @When /^I load Doctrine data from "(?P<tableName>.*)" using "(?P<connectionName>.*)"$/
     */
    public function iLoadDoctrineData($tableName, $connectionName = null)
    {
        $this->apiContext->expressionLanguageData[$tableName] = $this
            ->doctrine
            ->getConnection($connectionName)
            ->fetchAll('SELECT * FROM ' . $tableName);
    }
}
