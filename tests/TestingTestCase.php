<?php

namespace Test;

use ByJG\ApiTools\ApiRequester;
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
use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use JsonException;

/**
 * Class TestingTestCase
 *
 * @package Test
 * IMPORTANT: This class is base for the other tests
 * @see     OpenApiTestCaseTest
 * @see     SwaggerTestCaseTest
 */
abstract class TestingTestCase extends ApiTestCase
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
    public function testGet(): void
    {
        $request = new ApiRequester();
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
    public function testPost(): void
    {
        $body = [
            'id'        => 1,
            'name'      => 'Spike',
            'category'  => ['id' => 201, 'name' => 'dog'],
            'tags'      => [['id' => 2, 'name' => 'blackwhite']],
            'photoUrls' => [],
            'status'    => 'available',
        ];

        // Basic Request
        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath('/pet')
            ->withRequestBody($body);

        $this->assertRequest($request);

        // PSR7 Request
        $psr7Request = new Request('post', '/pet', [], Utils::streamFor(json_encode($body, JSON_THROW_ON_ERROR)));

        $expectedResponse = new Response();
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
    public function testAddError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'name\'');

        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath('/pet')
            ->withRequestBody([
                'id'        => 1,
                'category'  => ['id' => 201, 'name' => 'dog'],
                'tags'      => [['id' => 2, 'name' => 'blackwhite']],
                'photoUrls' => [],
                'status'    => 'available',
            ]);

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
    public function testPostError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Expected empty body');

        $request = new ApiRequester();
        $request
            ->withMethod('POST')
            ->withPath('/pet')
            ->withRequestBody([
                'id'        => 999, // <== The API will generate an invalid response for this ID
                'name'      => 'Spike',
                'category'  => ['id' => 201, 'name' => 'dog'],
                'tags'      => [['id' => 2, 'name' => 'blackwhite']],
                'photoUrls' => [],
                'status'    => 'available',
            ]);

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
    public function testMultipart(): void
    {
        $multipartStream = new MultipartStream([
            [
                'name'     => 'note',
                'contents' => 'somenote',
            ],
            [
                'name'     => 'upfile',
                'contents' => Utils::tryFopen(__DIR__ . '/smile.png', 'rb'),
            ],
        ]);

        $psr7Requester = new Request('post', '/inventory', ['Content-Type' => 'multipart/form-data; boundary=' . $multipartStream->getBoundary()], $multipartStream);

        $request = new ApiRequester();
        $request
            ->withPsr7Request($psr7Requester)
            ->assertResponseCode(200)
            ->assertBodyContains('smile.png')
            ->assertBodyContains('somenote');

        $this->assertRequest($request);
    }
}
