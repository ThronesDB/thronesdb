<?php

namespace AppBundle\Features\Context;

use Assert\Assertion;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Alsciende <alsciende@icloud.com>
 * @see https://github.com/imbo/behat-api-extension
 */
class ApiContext implements Context
{
    /**
     * Request options
     *
     * Options to send with the request.
     *
     * @var array
     */
    private $requestOptions = [];

    /**
     * @var string
     */
    private $content = '';

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $server = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $method;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var Response|null
     */
    private $response;

    /**
     * @var array
     */
    public $expressionLanguageData = [];

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Request a path
     *
     * @param string $path The path to request
     * @param string $method The HTTP method to use
     * @return self
     *
     * @When I request :path
     * @When I request :path using HTTP :method
     */
    public function requestPath($path, $method = null)
    {
        $this->setRequestPath($this->parseExpressionLanguageTemplate($path));
        if (null === $method) {
            $this->setRequestMethod('GET', false);
        } else {
            $this->setRequestMethod($method);
        }

        return $this->sendRequest();
    }

    /**
     * Update the path of the request
     *
     * @param string $path The path to request
     * @return self
     */
    private function setRequestPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * Update the HTTP method of the request
     *
     * @param string  $method The HTTP method
     * @param boolean $force Force the HTTP method. If set to false the method set CAN be
     *                       overridden (this occurs for instance when adding form parameters to the
     *                       request, and not specifying HTTP POST for the request)
     * @return self
     */
    private function setRequestMethod(string $method, bool $force = true)
    {
        $this->method = $method;
        $this->force = $force;
    }

    /**
     *
     */
    private function sendRequest()
    {
        $this->client->request($this->method, $this->path, $this->parameters, $this->files, $this->server, $this->content);
        $this->response = $this->client->getResponse();
    }

    /**
     * Assert the HTTP response code
     *
     * @param int $code The HTTP response code
     * @return void
     * @throws \Assert\AssertionFailedException
     *
     * @Then the response code is :code
     */
    public function assertResponseCodeIs(int $code)
    {
        $this->requireResponse();
        Assertion::same(
            $actual = $this->response->getStatusCode(),
            $expected = $this->validateResponseCode($code),
            sprintf('Expected response code %d, got %d.', $expected, $actual)
        );
    }

    /**
     * @throws \Assert\AssertionFailedException
     */
    private function requireResponse()
    {
        Assertion::notNull($this->response, 'The request has not been made yet, so no response object exists.');
    }

    /**
     * @param int $code
     * @return int
     * @throws \Assert\AssertionFailedException
     */
    private function validateResponseCode(int $code)
    {
        Assertion::range($code, 100, 599, sprintf('Response code must be between 100 and 599, got %d.', $code));

        return $code;
    }

    /**
     * Set the request body to a string
     *
     * @param resource|string|PyStringNode $body The content to set as the request body
     * @return self
     *
     * @Given the request body is:
     */
    public function setRequestBody(string $body)
    {
        if (!empty($this->requestOptions['multipart']) || !empty($this->requestOptions['form_params'])) {
            throw new \InvalidArgumentException(
                'It\'s not allowed to set a request body when using multipart/form-data or form parameters.'
            );
        }
        $this->content = $this->parseExpressionLanguageTemplate($body);

        return $this;
    }

    /**
     * @Given the request :name header is :value
     */
    public function setRequestHeader(string $name, string $value)
    {
        $this->server['http_'.$name] = $value;
    }

    /**
     * Considers all substrings surrounded by {{ }} as Expression Language expressions and evaluates them
     *
     * @param string $template
     * @return null|string|string[]
     */
    private function parseExpressionLanguageTemplate(string $template)
    {
        $expressionLanguage = new ExpressionLanguage();

        return preg_replace_callback('/{{(.*?)}}/U', function ($matches) use ($expressionLanguage) {
            return $expressionLanguage->evaluate(trim($matches[1]), ['data' => $this->expressionLanguageData]);
        }, $template);
    }
}