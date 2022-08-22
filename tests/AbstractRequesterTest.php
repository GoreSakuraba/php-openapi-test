<?php

namespace Test;

use ByJG\ApiTools\ApiTestCase;
use ByJG\ApiTools\Exception\DefinitionNotFoundException;
use ByJG\ApiTools\Exception\GenericSwaggerException;
use ByJG\ApiTools\Exception\HttpMethodNotFoundException;
use ByJG\ApiTools\Exception\InvalidDefinitionException;
use ByJG\ApiTools\Exception\InvalidRequestException;
use ByJG\ApiTools\Exception\NotMatchedException;
use ByJG\ApiTools\Exception\PathNotFoundException;
use ByJG\ApiTools\Exception\StatusCodeNotMatchedException;
use ByJG\ApiTools\MockRequester;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use JsonException;

abstract class AbstractRequesterTest extends ApiTestCase
{
    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testExpectOK(): void
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        // Basic Request
        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1');

        $this->assertRequest($request);

        // PSR7 Request
        $psr7Request = new Request('get', '/pet/1');

        $request = new MockRequester($expectedResponse);
        $request->withPsr7Request($psr7Request);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testExpectError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'name\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1');

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse(): void
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse404(): void
    {
        $expectedResponse = new Response(404);

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse404WithContent(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Expected empty body for \'GET 404 /v2/pet/1\'');

        $expectedResponse = new Response(404, [], Utils::streamFor('{"error":"not found"}'));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertResponseNotExpected(): void
    {
        $this->expectException(StatusCodeNotMatchedException::class);
        $this->expectExceptionMessage('Status code not matched: Expected \'404\', got \'522\'');

        $expectedResponse = new Response(522);

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContains(): void
    {
        $expectedResponse = new Response(200, ['X-Test' => 'Some Value to test'], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200)
            ->assertHeaderContains('X-Test', 'Value');

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContainsWrongValue(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Header \'X-Test\' does not contain value \'Different\'');

        $expectedResponse = new Response(200, ['X-Test' => 'Some Value to test'], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200)
            ->assertHeaderContains('X-Test', 'Different');

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContainsNonExistent(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Header \'X-Test\' does not contain value \'Different\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200)
            ->assertHeaderContains('X-Test', 'Different');

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertBodyContains(): void
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200)
            ->assertBodyContains('Spike');

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     */
    public function testValidateAssertBodyNotContains(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Body does not contain \'Doris\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            'id'        => 1,
            'name'      => 'Spike',
            'photoUrls' => [],
        ], JSON_THROW_ON_ERROR)));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath('/pet/1')
            ->assertResponseCode(200)
            ->assertBodyContains('Doris');

        $this->assertRequest($request);
    }
}
