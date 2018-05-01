<?php

declare(strict_types=1);

namespace AppBundle\Features\Json;

use Behat\Behat\Context\Argument\ArgumentResolver;

class JsonInspectorResolver implements ArgumentResolver
{
    /**
     * @var JsonInspector
     */
    private $jsonInspector;

    public function __construct(JsonInspector $jsonInspector)
    {
        $this->jsonInspector = $jsonInspector;
    }

    public function resolveArguments(\ReflectionClass $classReflection, array $arguments)
    {
        $constructor = $classReflection->getConstructor();
        if (null === $constructor) {
            return $arguments;
        }

        $parameters = $constructor->getParameters();
        foreach ($parameters as $parameter) {
            if (null !== $parameter->getClass() && 'Ubirak\RestApiBehatExtension\Json\JsonInspector' === $parameter->getClass()->name) {
                $arguments[$parameter->name] = $this->jsonInspector;
            }
        }

        return $arguments;
    }
}
