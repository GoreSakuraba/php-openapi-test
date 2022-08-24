<?php

namespace Test;

use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use JsonException;

class OpenApiResponseBodyTest extends OpenApiBodyTestCase
{
    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBody(): void
    {
        $openApiSchema = self::openApiSchema();

        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];

        $responseParameter = $openApiSchema->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));

        // Default
        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
        ];

        $responseParameter = $openApiSchema->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));

        // Number as string
        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];

        $responseParameter = $openApiSchema->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyWithRefInsteadOfContent(): void
    {
        $openApiSchema = self::openApiSchema5();

        $body = [
            'param_response_1' => 'example1',
            'param_response_2' => 'example2',
        ];

        $responseParameter = $openApiSchema->getResponseParameters('/v1/test', 'post', 201);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyEnumError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Value \'notfound\' in \'status\' not matched in ENUM');

        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'notfound',
            'complete' => true,
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyWrongNumber(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Expected \'id\' to be numeric, but found \'ABC\'');

        $body = [
            'id'       => 'ABC',
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyMoreThanExpected(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('The property(ies) \'more\' has not defined in \'#/components/schemas/Order\'');

        $body = [
            'id'       => 50,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
            'more'     => 'value',
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyLessFields(): void
    {
        $body = [
            'id'       => 10,
            'status'   => 'placed',
            'complete' => true,
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyAllowNullValues(): void
    {
        $body = [
            'id'       => 10,
            'status'   => 'placed',
            'complete' => null,
        ];

        $responseParameter = self::openApiSchema(true)->getResponseParameters(
            '/v2/store/ordernull',
            'post',
            200
        );
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyNotAllowNullValues(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Value of property \'complete\' is null, but should be of type \'boolean\'');

        $body = [
            'id'       => 10,
            'status'   => 'placed',
            'complete' => null,
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/store/order', 'post', 200);
        $responseParameter->match($body);
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyEmpty(): void
    {
        $body = null;

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/pet/10', 'get', 400);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyNotEmpty(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage('Expected empty body for');

        $body = ['suppose' => 'not here'];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/pet/10', 'get', 400);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyComplex(): void
    {
        $body = [
            'id'        => 10,
            'category'  => [
                'id'   => 1,
                'name' => 'Dog',
            ],
            'name'      => 'Spike',
            'photoUrls' => [
                'url1',
                'url2',
            ],
            'tags'      => [
                [
                    'id'   => 10,
                    'name' => 'cute',
                ],
                [
                    'name' => 'priceless',
                ],
            ],
            'status'    => 'available',
        ];

        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/pet/10', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchResponseBodyWhenValueWithNestedPropertiesIsNullAndNullsAreAllowed(): void
    {
        $body = [
            'id'        => 10,
            'category'  => null,
            'name'      => 'Spike',
            'photoUrls' => [
                'url1',
                'url2',
            ],
            'tags'      => [
                [
                    'id'   => 10,
                    'name' => 'cute',
                ],
                [
                    'name' => 'priceless',
                ],
            ],
            'status'    => 'available',
        ];

        $responseParameter = self::openApiSchema(true)->getResponseParameters('/v2/pet/10', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testAdditionalPropertiesInObjectInResponseBody(): void
    {
        $body = ['value1' => 1, 'value2' => 2];
        $responseParameter = self::openApiSchema5()->getResponseParameters('/tests/additional_properties', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testAdditionalPropertiesInObjectInResponseBodyDoNotMatch(): void
    {
        $this->expectExceptionMessage('Expected \'value2\' to be numeric, but found \'string\'');
        $this->expectException(NotMatchedException::class);
        $body = ['value1' => 1, 'value2' => 'string'];
        $responseParameter = self::openApiSchema5()->getResponseParameters('/tests/additional_properties', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * Issue #9
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testIssue9(): void
    {
        $body = [
            [
                [
                    'isoCode'   => 'fr',
                    'label'     => 'French',
                    'isDefault' => true,
                ],
                [
                    'isoCode'   => 'br',
                    'label'     => 'Brazilian',
                    'isDefault' => false,
                ],
            ],
        ];

        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/languages', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * Issue #9
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testIssue9Error(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessageMatches('"I expected an array here.*"');

        $body = [
            [
                'isoCode'   => 'fr',
                'label'     => 'French',
                'isDefault' => true,
            ],
            [
                'isoCode'   => 'br',
                'label'     => 'Brazilian',
                'isDefault' => false,
            ],
        ];

        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/languages', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * Issue #9
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchAnyValue(): void
    {
        $body = 'string';
        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        $body = 1000;
        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        $body = ['test' => 10];
        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testMatchAllOf(): void
    {
        $body = ['name' => 'Bob', 'email' => 'bob@example.com'];
        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/allof', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/allofref', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        // password is not required
        $responseParameter = self::openApiSchema2()->getResponseParameters('/v2/nestedallofref', 'get', 200);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testResponseDefault(): void
    {
        $body = [];
        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/user', 'post', 503);
        $this->assertTrue($responseParameter->match($body));
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws JsonException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testResponseWithNoDefault(): void
    {
        $this->expectException(InvalidDefinitionException::class);
        $this->expectExceptionMessage('Could not find status code \'503\'');

        $body = [];
        $responseParameter = self::openApiSchema()->getResponseParameters('/v2/user/login', 'get', 503);
    }
}
