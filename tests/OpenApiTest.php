<?php

namespace Test;

use GoreSakuraba\OpenAPI\Base\Schema;
use JsonException;

require_once 'AbstractRequesterTest.php';

class OpenApiTest extends AbstractRequesterTest
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
