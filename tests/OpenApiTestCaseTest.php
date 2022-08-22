<?php

namespace Test;

use ByJG\ApiTools\Base\Schema;
use JsonException;

class OpenApiTestCaseTest extends TestingTestCase
{
    /**
     * @return void
     * @throws JsonException
     */
    public function setUp(): void
    {
        $schema = Schema::getInstance(file_get_contents(__DIR__ . '/rest/openapi.json'));
        $this->setSchema($schema);
    }
}
