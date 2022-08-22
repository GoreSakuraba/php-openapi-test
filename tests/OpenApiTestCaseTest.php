<?php

namespace Test;

use GoreSakuraba\OpenAPI\Base\Schema;
use JsonException;

class OpenApiTestCaseTest extends TestingTestCase
{
    /**
     * @return void
     * @throws JsonException
     */
    public function setUp(): void
    {
        $schema = Schema::getInstance(file_get_contents(__DIR__ . '/Rest/openapi.json'));
        $this->setSchema($schema);
    }
}
