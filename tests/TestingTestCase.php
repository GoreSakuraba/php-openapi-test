<?php

namespace Test;

use ByJG\ApiTools\ApiRequester;
use ByJG\ApiTools\ApiTestCase;
use ByJG\ApiTools\MockRequester;
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;

/**
 * Class TestingTestCase
 * @package Test
 *
 * IMPORTANT: This class is base for the other tests
 *
 * @see OpenApiTestCaseTest
 * @see SwaggerTestCaseTest
 */
abstract class TestingTestCase extends ApiTestCase
{

    public function testGet()
    {
        $request = new ApiRequester();
        $request
            ->withMethod('GET')
            ->withPath("/pet/1");

        $this->assertRequest($request);
    }

    public function testPost()
    {
        $body = [
            'id' => 1,
            'name' => 'Spike',
            'category' => [ 'id' => 201, 'name' => 'dog'],
            'tags' => [[ 'id' => 2, 'name' => 'blackwhite']],
            'photoUrls' => [],
            'status' => 'available'
        ];

        // Basic Request
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/pet")
            ->withRequestBody($body);

        $this->assertRequest($request);


        // PSR7 Request
        $psr7Request = new Request('post', '/pet', [], Utils::streamFor(json_encode($body)));

        $expectedResponse = new Response();
        $request = new MockRequester($expectedResponse);
        $request->withPsr7Request($psr7Request);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException

     */
    public function testAddError()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'name\'');

        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/pet")
            ->withRequestBody([
                'id' => 1,
                'category' => [ 'id' => 201, 'name' => 'dog'],
                'tags' => [[ 'id' => 2, 'name' => 'blackwhite']],
                'photoUrls' => [],
                'status' => 'available'
            ]);

        $this->assertRequest($request);
    }

    /**
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException

     */
    public function testPostError()
    {
        $this->expectException(\ByJG\ApiTools\Exception\NotMatchedException::class);
        $this->expectExceptionMessage('Expected empty body');

        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath("/pet")
            ->withRequestBody([
                'id' => 999, // <== The API will generate an invalid response for this ID
                'name' => 'Spike',
                'category' => [ 'id' => 201, 'name' => 'dog'],
                'tags' => [[ 'id' => 2, 'name' => 'blackwhite']],
                'photoUrls' => [],
                'status' => 'available'
            ]);

        $this->assertRequest($request);
    }

    /**
     * @return void
     * @throws \ByJG\ApiTools\Exception\DefinitionNotFoundException
     * @throws \ByJG\ApiTools\Exception\GenericSwaggerException
     * @throws \ByJG\ApiTools\Exception\HttpMethodNotFoundException
     * @throws \ByJG\ApiTools\Exception\InvalidDefinitionException
     * @throws \ByJG\ApiTools\Exception\InvalidRequestException
     * @throws \ByJG\ApiTools\Exception\NotMatchedException
     * @throws \ByJG\ApiTools\Exception\PathNotFoundException
     * @throws \ByJG\ApiTools\Exception\StatusCodeNotMatchedException
     */
    public function testMultipart()
    {
        $psr7Requester = new Request('post', '/inventory', ['Content-Type' => 'multipart/form-data'], new MultipartStream([
            [
                'name'     => 'note',
                'contents' => 'somenote'
            ],
            [
                'name'     => 'upfile',
                'contents' => Utils::tryFopen(__DIR__ . '/smile.png', 'rb'),
            ],
        ]));

        $request = new ApiRequester();
        $request
            ->withPsr7Request($psr7Requester)
            ->assertResponseCode(200)
            ->assertBodyContains("smile")
            ->assertBodyContains("somenote");

        $this->assertRequest($request);
    }
}
