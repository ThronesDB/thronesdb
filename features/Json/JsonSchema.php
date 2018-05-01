<?php

declare(strict_types=1);

namespace AppBundle\Features\Json;

use JsonSchema\SchemaStorage;
use JsonSchema\Validator;

class JsonSchema
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @param string
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    /**
     * @param Json          $json
     * @param Validator     $validator
     * @param SchemaStorage $schemaStorage
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validate(Json $json, Validator $validator, SchemaStorage $schemaStorage)
    {
        $schema = $schemaStorage->resolveRef('file://' . realpath($this->filename));
        $data = $json->getRawContent();

        $validator->check($data, $schema);

        if (!$validator->isValid()) {
            $msg = 'JSON does not validate. Violations:' . PHP_EOL;
            foreach ($validator->getErrors() as $error) {
                $msg .= sprintf('  - [%s] %s' . PHP_EOL, $error['property'], $error['message']);
            }
            throw new \Exception($msg);
        }

        return true;
    }
}
