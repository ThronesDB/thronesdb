<?php

declare(strict_types=1);

namespace AppBundle\Features\Json;

use JsonSchema\Validator;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Uri\UriResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class JsonParser
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor()
        ;
    }

    /**
     * @param Json   $json
     * @param string $expression
     *
     * @return array|mixed
     *
     * @throws \Exception
     */
    public function evaluate(Json $json, string $expression)
    {
        try {
            return $json->read($expression, $this->propertyAccessor);
        } catch (\Exception $e) {
            throw new \Exception(sprintf('Failed to evaluate expression "%s"', $expression), 0, $e);
        }
    }

    /**
     * @param Json       $json
     * @param JsonSchema $schema
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validate(Json $json, JsonSchema $schema)
    {
        return $schema->validate($json, new Validator(), new SchemaStorage(new UriRetriever(), new UriResolver()));
    }
}
