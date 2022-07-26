<?php

namespace Test;

use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GoreSakuraba\OpenAPI\Exception\RequiredArgumentNotFound;
use JsonException;

class OpenApiRequestBodyTest extends OpenApiBodyTestCase
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
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBody(): void
    {
        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/store/order', 'post');
        $this->assertTrue($requestParameter->match($body));
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
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequiredRequestBodyEmpty(): void
    {
        $this->expectException(RequiredArgumentNotFound::class);
        $this->expectExceptionMessage('The body is required');

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/store/order', 'post');
        $this->assertTrue($requestParameter->match(''));
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
     * @throws RequiredArgumentNotFound
     */
    public function testMatchInexistentBodyDefinition(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Body is passed but there is no request body definition');

        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/pet/1', 'get');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchDataType(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Path expected an integer value');

        self::openApiSchema()->getRequestParameters('/v2/pet/STRING', 'get');
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchParameterInQuery(): void
    {
        // @todo Validate parameters in query
        self::openApiSchema()->getRequestParameters('/v2/pet/findByStatus?status=pending', 'get');
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchParameterInQuery2(): void
    {
        self::openApiSchema3()->getRequestParameters('/tests/12345?count=20&offset=2', 'get');
        $this->assertTrue(true);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchParameterInQuery3(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Path expected an integer value');

        self::openApiSchema3()->getRequestParameters('/tests/STRING?count=20&offset=2', 'get');
        $this->assertTrue(true);
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
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequired1(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Required property');

        $body = [
            'id'     => 10,
            'status' => 'pending',
        ];

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/pet', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * It is not OK when allowNullValues is false (as by default) { name: null }
     * https://stackoverflow.com/questions/45575493/what-does-required-in-openapi-really-mean
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequiredNullsNotAllowed(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Value of property \'name\' is null, but should be of type \'string\'');

        $body = [
            'id'        => 10,
            'status'    => 'pending',
            'name'      => null,
            'photoUrls' => ['http://example.com/1', 'http://example.com/2'],
        ];

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/pet', 'post');
        $this->assertTrue($requestParameter->match($body));
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
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequiredNullsAllowed(): void
    {
        $body = [
            'id'        => 10,
            'status'    => 'pending',
            'name'      => null,
            'photoUrls' => ['http://example.com/1', 'http://example.com/2'],
        ];

        $requestParameter = self::openApiSchema(true)->getRequestParameters('/v2/petnull', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * It is OK: { name: ""}
     * https://stackoverflow.com/questions/45575493/what-does-required-in-openapi-really-mean
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequired3(): void
    {
        $body = [
            'id'        => 10,
            'status'    => 'pending',
            'name'      => '',
            'photoUrls' => ['http://example.com/1', 'http://example.com/2'],
        ];

        $requestParameter = self::openApiSchema()->getRequestParameters('/v2/pet', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * issue #21
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequiredIssue21(): void
    {
        // Full Request
        $body = [
            'wallet_uuid' => '502a1aa3-5239-4d4b-af09-4dc24ac5f034',
            'user_uuid'   => 'e7f6c18b-8094-4c2c-9987-1be5b7c46678',
        ];

        $requestParameter = self::openApiSchema2()->getRequestParameters('/accounts/create', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * issue #21
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws RequiredArgumentNotFound
     */
    public function testMatchRequestBodyRequiredIssue21Required(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'user_uuid\'');

        // Missing Request
        $body = [
            'wallet_uuid' => '502a1aa3-5239-4d4b-af09-4dc24ac5f034',
        ];

        $requestParameter = self::openApiSchema2()->getRequestParameters('/accounts/create', 'post');
        $requestParameter->match($body);
    }
}
