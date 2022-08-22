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

class SwaggerRequestBodyTest extends SwaggerBodyTestCase
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
        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/store/order', 'post');
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

        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/store/order', 'post');
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

        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/pet/1', 'get');
        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];
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

        self::swaggerSchema()->getRequestParameters('/v2/pet/STRING', 'get');
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
        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/pet', 'post');
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
        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/pet', 'post');
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
        $requestParameter = self::swaggerSchema(true)->getRequestParameters('/v2/pet', 'post');
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
        $requestParameter = self::swaggerSchema()->getRequestParameters('/v2/pet', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * Issue #21
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
    public function testMatchRequestBodyRequired_Issue21(): void
    {
        // Full Request
        $body = [
            'wallet_uuid' => '502a1aa3-5239-4d4b-af09-4dc24ac5f034',
            'user_uuid'   => 'e7f6c18b-8094-4c2c-9987-1be5b7c46678',
        ];
        $requestParameter = self::swaggerSchema2()->getRequestParameters('/accounts/create', 'post');
        $this->assertTrue($requestParameter->match($body));
    }

    /**
     * Issue #21
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
    public function testMatchRequestBodyRequired_Issue21_Required(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Required property \'user_uuid\'');

        // Missing Request
        $body = [
            'wallet_uuid' => '502a1aa3-5239-4d4b-af09-4dc24ac5f034',
        ];
        $requestParameter = self::swaggerSchema2()->getRequestParameters('/accounts/create', 'post');
        $requestParameter->match($body);
    }
}
