<?php

namespace Test;

use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use JsonException;
use PHPUnit\Framework\TestCase;

class BaseExceptionTest extends TestCase
{
    /**
     * @return void
     * @throws JsonException
     */
    public function testGetBody(): void
    {
        $exception = new GenericSwaggerException('message', ['a' => 10]);

        $this->assertEquals("message ->\n{\n    \"a\": 10\n}\n", $exception->getMessage());
        $this->assertEquals(['a' => 10], $exception->getBody());
    }
}
