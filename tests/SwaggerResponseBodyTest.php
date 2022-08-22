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

class SwaggerResponseBodyTest extends SwaggerBodyTestCase
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
        $schema = self::swaggerSchema();

        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
        ];
        $responseParameter = $schema->getResponseParameters('/v2/store/order', 'post', 200);
        $this->assertTrue($responseParameter->match($body));

        // Default
        $body = [
            'id'       => 10,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
        ];
        $responseParameter = $schema->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = $schema->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/store/order', 'post', 200);
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
        $this->expectExceptionMessage('The property(ies) \'more\' has not defined in \'#/definitions/Order\'');

        $body = [
            'id'       => 50,
            'petId'    => 50,
            'quantity' => 1,
            'shipDate' => '2010-10-20',
            'status'   => 'placed',
            'complete' => true,
            'more'     => 'value',
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = self::swaggerSchema(true)->getResponseParameters(
            '/v2/store/order',
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/store/order', 'post', 200);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/10', 'get', 400);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/10', 'get', 400);
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
                    'id'   => '10',
                    'name' => 'cute',
                ],
                [
                    'name' => 'priceless',
                ],
            ],
            'status'    => 'available',
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/10', 'get', 200);
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
                    'id'   => '10',
                    'name' => 'cute',
                ],
                [
                    'name' => 'priceless',
                ],
            ],
            'status'    => 'available',
        ];
        $responseParameter = self::swaggerSchema(true)->getResponseParameters('/v2/pet/10', 'get', 200);
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
    public function testNotMatchResponseBodyWhenValueWithPatterns(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage(<<<'EOL'
Value '18' in 'age' not matched in pattern. ->
{
    "description": "successful operation",
    "schema": {
        "$ref": "#\/definitions\/DateShelter"
    }
}
EOL
        );

        $body = [
            'date' => '2010-05-11',
            'age'  => 18,
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/dateShelter', 'get', 200);
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
    public function testMatchResponseBodyWhenValueWithPatterns(): void
    {
        $body = [
            'date' => '2010-05-11',
            'age'  => '18',
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/dateShelter', 'get', 200);
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
    public function testMatchResponseBodyWhenValueWithStringPatternError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage(<<<'EOL'
Value '20100-05-11' in 'date' not matched in pattern. ->
{
    "description": "successful operation",
    "schema": {
        "$ref": "#\/definitions\/DateShelter"
    }
}
EOL
        );

        $body = [
            'date' => '20100-05-11',
            'age'  => 18,
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/dateShelter', 'get', 200);
        $this->assertFalse($responseParameter->match($body));
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
    public function testMatchResponseBodyWhenValueWithNumberPatternError(): void
    {
        $this->expectException(NotMatchedException::class);
        $this->expectExceptionMessage(<<<'EOL'
Value '9999' in 'age' not matched in pattern. ->
{
    "description": "successful operation",
    "schema": {
        "$ref": "#\/definitions\/DateShelter"
    }
}
EOL
        );

        $body = [
            'date' => '2010-05-11',
            'age'  => 9999,
        ];
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/pet/dateShelter', 'get', 200);
        $this->assertFalse($responseParameter->match($body));
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
        $responseParameter = self::swaggerSchema2()->getResponseParameters('/v2/languages', 'get', 200);
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
        $responseParameter = self::swaggerSchema2()->getResponseParameters('/v2/languages', 'get', 200);
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
        $responseParameter = self::swaggerSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        $body = 1000;
        $responseParameter = self::swaggerSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
        $this->assertTrue($responseParameter->match($body));

        $body = ['test' => '10'];
        $responseParameter = self::swaggerSchema2()->getResponseParameters('/v2/anyvalue', 'get', 200);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/user', 'post', 503);
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
        $responseParameter = self::swaggerSchema()->getResponseParameters('/v2/user/login', 'get', 503);
    }
}
