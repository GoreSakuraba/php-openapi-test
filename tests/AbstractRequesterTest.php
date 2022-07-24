<?php

namespace Test;

use ByJG\ApiTools\ApiTestCase;
use ByJG\ApiTools\MockRequester;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

abstract class AbstractRequesterTest extends ApiTestCase
{
    public function testExpectOK()
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        // Basic Request
        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1");

        $this->assertRequest($request);

        // PSR7 Request
        $psr7Request = new Request('get', '/pet/1');

        $request = new MockRequester($expectedResponse);
        $request->withPsr7Request($psr7Request);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testExpectError()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'name\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1");

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse()
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse404()
    {
        $expectedResponse = new Response(404);

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertResponse404WithContent()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Expected empty body for GET 404 /v2/pet/1');

        $expectedResponse = new Response(404, [], Utils::streamFor('{"error":"not found"}'));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertResponseNotExpected()
    {
        $this->expectException(\ByJG\ApiTools\Exception\StatusCodeNotMatchedException::class);
        $this->expectExceptionMessage('Status code not matched: Expected 404, got 522');

        $expectedResponse = new Response(522);

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(404);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContains()
    {
        $expectedResponse = new Response(200, ['X-Test' => 'Some Value to test'], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200)
            ->assertHeaderContains("X-Test", "Value");

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContainsWrongValue()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Does not exists header \'X-Test\' with value \'Different\'');

        $expectedResponse = new Response(200, ['X-Test' => 'Some Value to test'], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200)
            ->assertHeaderContains("X-Test", "Different");

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertHeaderContainsNonExistent()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Does not exists header \'X-Test\' with value \'Different\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200)
            ->assertHeaderContains("X-Test", "Different");

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertBodyContains()
    {
        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200)
            ->assertBodyContains("Spike");

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testValidateAssertBodyNotContains()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Body does not contain \'Doris\'');

        $expectedResponse = new Response(200, [], Utils::streamFor(json_encode([
            "id" => 1,
            "name" => "Spike",
            "photoUrls" => []
        ])));

        $request = new MockRequester($expectedResponse);
        $request
            ->withMethod('GET')
            ->withPath("/pet/1")
            ->assertResponseCode(200)
            ->assertBodyContains("Doris");

        $this->assertRequest($request);
    }
}
