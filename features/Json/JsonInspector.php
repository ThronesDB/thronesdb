<?php

declare(strict_types=1);

namespace AppBundle\Features\Json;

class JsonInspector
{
    /**
     * @var JsonParser
     */
    private $jsonParser;

    /**
     * @var JsonStorage
     */
    private $jsonStorage;

    public function __construct(JsonStorage $jsonStorage, JsonParser $jsonParser)
    {
        $this->jsonParser = $jsonParser;
        $this->jsonStorage = $jsonStorage;
    }

    /**
     * @param $jsonNodeExpression
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function readJsonNodeValue($jsonNodeExpression)
    {
        return $this->jsonParser->evaluate(
            $this->readJson(),
            $jsonNodeExpression
        );
    }

    public function readJson()
    {
        return $this->jsonStorage->readJson();
    }

    /**
     * @param JsonSchema $jsonSchema
     *
     * @throws \Exception
     */
    public function validateJson(JsonSchema $jsonSchema)
    {
        $this->jsonParser->validate(
            $this->readJson(),
            $jsonSchema
        );
    }

    public function writeJson($jsonContent)
    {
        $this->jsonStorage->writeRawContent($jsonContent);
    }
}
