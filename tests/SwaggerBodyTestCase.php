<?php

namespace Test;

use ByJG\ApiTools\Base\Schema;
use ByJG\ApiTools\Swagger\SwaggerSchema;
use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * baseclass for further tests
 *
 * @see SwaggerRequestBodyTest
 * @see SwaggerResponseBodyTest
 */
class SwaggerBodyTestCase extends TestCase
{
    /**
     * @param bool $allowNullValues
     *
     * @return SwaggerSchema
     * @throws JsonException
     */
    protected static function swaggerSchema(bool $allowNullValues = false): SwaggerSchema
    {
        /** @var SwaggerSchema $schema */
        $schema = Schema::getInstance(
            self::getSwaggerJsonContent(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @param bool $allowNullValues
     *
     * @return SwaggerSchema
     * @throws JsonException
     */
    protected static function swaggerSchema2(bool $allowNullValues = false): SwaggerSchema
    {
        /** @var SwaggerSchema $schema */
        $schema = Schema::getInstance(
            self::getSwaggerJsonContent_No2(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @return string
     */
    protected static function getSwaggerJsonContent(): string
    {
        return file_get_contents(__DIR__ . '/example/swagger.json');
    }

    /**
     * @return string
     */
    protected static function getSwaggerJsonContent_No2(): string
    {
        return file_get_contents(__DIR__ . '/example/swagger2.json');
    }
}
