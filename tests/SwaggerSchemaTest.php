<?php

namespace Test;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GoreSakuraba\OpenAPI\Swagger\SwaggerSchema;
use JsonException;
use PHPUnit\Framework\TestCase;

class SwaggerSchemaTest extends TestCase
{
    /**
     * @var SwaggerSchema|null
     */
    protected ?SwaggerSchema $schema = null;

    /**
     * @return void
     * @throws JsonException
     */
    public function setUp(): void
    {
        /** @var SwaggerSchema $schema */
        $schema = Schema::getInstance(file_get_contents(__DIR__ . '/example/swagger.json'));

        $this->schema = $schema;
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->schema = null;
    }

    /**
     * @return void
     */
    public function testGetBasePath(): void
    {
        $this->assertEquals('/v2', $this->schema->getBasePath());
    }

    /**
     * @return void
     * @throws HttpMethodNotFoundException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetPathDirectMatch(): void
    {
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Add a new pet to the store',
                'description' => '',
                'operationId' => 'addPet',
                'consumes'    => [
                    'application/json',
                    'application/xml',
                ],
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'in'          => 'body',
                        'name'        => 'body',
                        'description' => 'Pet object that needs to be added to the store',
                        'required'    => true,
                        'schema'      => [
                            '$ref' => '#/definitions/Pet',
                        ],
                    ],
                ],
                'responses'   => [
                    '405' => [
                        'description' => 'Invalid input',
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet', 'post')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Update an existing pet',
                'description' => '',
                'operationId' => 'updatePet',
                'consumes'    => [
                    'application/json',
                    'application/xml',
                ],
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'in'          => 'body',
                        'name'        => 'body',
                        'description' => 'Pet object that needs to be added to the store',
                        'required'    => true,
                        'schema'      => [
                            '$ref' => '#/definitions/Pet',
                        ],
                    ],
                ],
                'responses'   => [
                    '400' => [
                        'description' => 'Invalid ID supplied',
                    ],
                    '404' => [
                        'description' => 'Pet not found',
                    ],
                    '405' => [
                        'description' => 'Validation exception',
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet', 'put')
        );
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testGetPathPatternMatch(): void
    {
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Find pet by ID',
                'description' => 'Returns a single pet',
                'operationId' => 'getPetById',
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet to return',
                        'required'    => true,
                        'type'        => 'integer',
                        'format'      => 'int64',
                    ],
                ],
                'responses'   => [
                    '200' => [
                        'description' => 'successful operation',
                        'schema'      => [
                            '$ref' => '#/definitions/Pet',
                        ],
                    ],
                    '400' => [
                        'description' => 'Invalid ID supplied',
                    ],
                    '404' => [
                        'description' => 'Pet not found',
                    ],
                ],
                'security'    => [
                    [
                        'api_key' => [],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet/10', 'get')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Updates a pet in the store with form data',
                'description' => '',
                'operationId' => 'updatePetWithForm',
                'consumes'    => [
                    'application/x-www-form-urlencoded',
                ],
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet that needs to be updated',
                        'required'    => true,
                        'type'        => 'integer',
                        'format'      => 'int64',
                    ],
                    [
                        'name'        => 'name',
                        'in'          => 'formData',
                        'description' => 'Updated name of the pet',
                        'required'    => false,
                        'type'        => 'string',
                    ],
                    [
                        'name'        => 'status',
                        'in'          => 'formData',
                        'description' => 'Updated status of the pet',
                        'required'    => false,
                        'type'        => 'string',
                    ],
                ],
                'responses'   => [
                    '405' => [
                        'description' => 'Invalid input',
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet/10', 'post')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Deletes a pet',
                'description' => '',
                'operationId' => 'deletePet',
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'name'     => 'api_key',
                        'in'       => 'header',
                        'required' => false,
                        'type'     => 'string',
                    ],
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'Pet id to delete',
                        'required'    => true,
                        'type'        => 'integer',
                        'format'      => 'int64',
                    ],
                ],
                'responses'   => [
                    '400' => [
                        'description' => 'Invalid ID supplied',
                    ],
                    '404' => [
                        'description' => 'Pet not found',
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet/10', 'delete')
        );
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testGetPathPatternMatch2(): void
    {
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'uploads an image',
                'description' => '',
                'operationId' => 'uploadFile',
                'consumes'    => [
                    'multipart/form-data',
                ],
                'produces'    => [
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet to update',
                        'required'    => true,
                        'type'        => 'integer',
                        'format'      => 'int64',
                    ],
                    [
                        'name'        => 'additionalMetadata',
                        'in'          => 'formData',
                        'description' => 'Additional data to pass to server',
                        'required'    => false,
                        'type'        => 'string',
                    ],
                    [
                        'name'        => 'file',
                        'in'          => 'formData',
                        'description' => 'file to upload',
                        'required'    => false,
                        'type'        => 'file',
                    ],
                ],
                'responses'   => [
                    '200' => [
                        'description' => 'successful operation',
                        'schema'      => [
                            '$ref' => '#/definitions/ApiResponse',
                        ],
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $this->schema->getPathDefinition('/v2/pet/10/uploadImage', 'post')
        );
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testGetPathFail(): void
    {
        $this->expectException(PathNotFoundException::class);

        $this->schema->getPathDefinition('/v2/pets', 'get');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testPathExistsButMethodDont(): void
    {
        $this->expectException(HttpMethodNotFoundException::class);

        $this->schema->getPathDefinition('/v2/pet', 'GET');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function testGetPathStructure(): void
    {
        $pathDefinition = $this->schema->getPathDefinition('/v2/pet', 'PUT');

        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Update an existing pet',
                'description' => '',
                'operationId' => 'updatePet',
                'consumes'    => [
                    'application/json',
                    'application/xml',
                ],
                'produces'    => [
                    'application/xml',
                    'application/json',
                ],
                'parameters'  => [
                    [
                        'in'          => 'body',
                        'name'        => 'body',
                        'description' => 'Pet object that needs to be added to the store',
                        'required'    => true,
                        'schema'      => [
                            '$ref' => '#/definitions/Pet',
                        ],
                    ],
                ],
                'responses'   => [
                    '400' => [
                        'description' => 'Invalid ID supplied',
                    ],
                    '404' => [
                        'description' => 'Pet not found',
                    ],
                    '405' => [
                        'description' => 'Validation exception',
                    ],
                ],
                'security'    => [
                    [
                        'petstore_auth' => [
                            'write:pets',
                            'read:pets',
                        ],
                    ],
                ],
            ],
            $pathDefinition
        );
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinitionFailed(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $this->schema->getDefinition('Order');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinitionFailed2(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $this->schema->getDefinition('1/2/Order');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinitionFailed3(): void
    {
        $this->expectException(DefinitionNotFoundException::class);

        $this->schema->getDefinition('#/definitions/OrderNOtFound');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinition(): void
    {
        $order = $this->schema->getDefinition('#/definitions/Order');

        $this->assertEquals(
            [
                'type'                 => 'object',
                'additionalProperties' => false,
                'properties'           => [
                    'id'       => [
                        'type'   => 'integer',
                        'format' => 'int64',
                    ],
                    'petId'    => [
                        'type'   => 'integer',
                        'format' => 'int64',
                    ],
                    'quantity' => [
                        'type'   => 'integer',
                        'format' => 'int32',
                    ],
                    'shipDate' => [
                        'type'   => 'string',
                        'format' => 'date-time',
                    ],
                    'status'   => [
                        'type'        => 'string',
                        'description' => 'Order Status',
                        'enum'        => [
                            'placed',
                            'approved',
                            'delivered',
                        ],
                    ],
                    'complete' => [
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                ],
                'xml'                  => [
                    'name' => 'Order',
                ],
            ],
            $order
        );
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testItNotAllowsNullValuesByDefault(): void
    {
        $schema = Schema::getInstance('{"swagger": "2.0"}');
        $this->assertFalse($schema->isAllowNullValues());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testItAllowsNullValues(): void
    {
        $schema = Schema::getInstance('{"swagger": "2.0"}', true);
        $this->assertTrue($schema->isAllowNullValues());
    }
}
