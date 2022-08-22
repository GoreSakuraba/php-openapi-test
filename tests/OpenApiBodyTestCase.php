<?php

namespace Test;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\OpenApi\OpenApiSchema;
use JsonException;
use PHPUnit\Framework\TestCase;

/**
 * baseclass for further tests
 *
 * @see OpenApiRequestBodyTest
 * @see OpenApiResponseBodyTest
 */
class OpenApiBodyTestCase extends TestCase
{
    /**
     * @param bool $allowNullValues
     *
     * @return OpenApiSchema
     * @throws JsonException
     */
    protected static function openApiSchema(bool $allowNullValues = false): OpenApiSchema
    {
        /** @var OpenApiSchema $schema */
        $schema = Schema::getInstance(
            self::getOpenApiJsonContent(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @param bool $allowNullValues
     *
     * @return OpenApiSchema
     * @throws JsonException
     */
    protected static function openApiSchema2(bool $allowNullValues = false): OpenApiSchema
    {
        /** @var OpenApiSchema $schema */
        $schema = Schema::getInstance(
            self::getOpenApiJsonContent_No2(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @param bool $allowNullValues
     *
     * @return OpenApiSchema
     * @throws JsonException
     */
    protected static function openApiSchema3(bool $allowNullValues = false): OpenApiSchema
    {
        /** @var OpenApiSchema $schema */
        $schema = Schema::getInstance(
            self::getOpenApiJsonContent_No3(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @param bool $allowNullValues
     *
     * @return OpenApiSchema
     * @throws JsonException
     */
    protected static function openApiSchema5(bool $allowNullValues = false): OpenApiSchema
    {
        /** @var OpenApiSchema $schema */
        $schema = Schema::getInstance(
            self::getOpenApiJsonContent_No5(),
            $allowNullValues
        );

        return $schema;
    }

    /**
     * @return string
     */
    protected static function getOpenApiJsonContent(): string
    {
        return file_get_contents(__DIR__ . '/example/openapi.json');
    }

    /**
     * @return string
     */
    protected static function getOpenApiJsonContent_No2(): string
    {
        return file_get_contents(__DIR__ . '/example/openapi2.json');
    }

    /**
     * @return string
     */
    protected static function getOpenApiJsonContent_No3(): string
    {
        return file_get_contents(__DIR__ . '/example/openapi3.json');
    }

    /**
     * @return string
     */
    protected static function getOpenApiJsonContent_No5(): string
    {
        return file_get_contents(__DIR__ . '/example/openapi5.json');
    }
}
