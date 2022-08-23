<?php

namespace Test;

use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GoreSakuraba\OpenAPI\OpenApi\OpenApiSchema;
use JsonException;
use PHPUnit\Framework\TestCase;

class OpenApiSchemaTest extends TestCase
{
    protected ?OpenApiSchema $openapiObject = null;

    /**
     * @return void
     * @throws JsonException
     */
    public function setUp(): void
    {
        $this->openapiObject = new OpenApiSchema(file_get_contents(__DIR__ . '/example/openapi.json'));
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        $this->openapiObject = null;
    }

    /**
     * @return void
     */
    public function testGetBasePath(): void
    {
        $this->assertEquals('/v2', $this->openapiObject->getBasePath());
    }

    /**
     * @return void
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws DefinitionNotFoundException
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
                'requestBody' => [
                    '$ref' => '#/components/requestBodies/Pet',
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
            $this->openapiObject->getPathDefinition('/v2/pet', 'post')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Update an existing pet',
                'description' => '',
                'operationId' => 'updatePet',
                'requestBody' => [
                    '$ref' => '#/components/requestBodies/Pet',
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
            $this->openapiObject->getPathDefinition('/v2/pet', 'put')
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
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet to return',
                        'required'    => true,
                        'schema'      => [
                            'type'   => 'integer',
                            'format' => 'int64',
                        ],
                    ],
                ],
                'responses'   => [
                    '200' => [
                        'description' => 'successful operation',
                        'content'     => [
                            'application/xml'  => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Pet',
                                ],
                            ],
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Pet',
                                ],
                            ],
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
            $this->openapiObject->getPathDefinition('/v2/pet/10', 'get')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Updates a pet in the store with form data',
                'description' => '',
                'operationId' => 'updatePetWithForm',
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet that needs to be updated',
                        'required'    => true,
                        'schema'      => [
                            'type'   => 'integer',
                            'format' => 'int64',
                        ],
                    ],
                ],
                'requestBody' => [
                    'content' => [
                        'application/x-www-form-urlencoded' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'name'   => [
                                        'description' => 'Updated name of the pet',
                                        'type'        => 'string',
                                    ],
                                    'status' => [
                                        'description' => 'Updated status of the pet',
                                        'type'        => 'string',
                                    ],
                                ],
                            ],
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
            $this->openapiObject->getPathDefinition('/v2/pet/10', 'post')
        );
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Deletes a pet',
                'description' => '',
                'operationId' => 'deletePet',
                'parameters'  => [
                    [
                        'name'     => 'api_key',
                        'in'       => 'header',
                        'required' => false,
                        'schema'   => [
                            'type' => 'string',
                        ],
                    ],
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'Pet id to delete',
                        'required'    => true,
                        'schema'      => [
                            'type'   => 'integer',
                            'format' => 'int64',
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
            $this->openapiObject->getPathDefinition('/v2/pet/10', 'delete')
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
                'parameters'  => [
                    [
                        'name'        => 'petId',
                        'in'          => 'path',
                        'description' => 'ID of pet to update',
                        'required'    => true,
                        'schema'      => [
                            'type'   => 'integer',
                            'format' => 'int64',
                        ],
                    ],
                ],
                'requestBody' => [
                    'content' => [
                        'multipart/form-data' => [
                            'schema' => [
                                'type'       => 'object',
                                'properties' => [
                                    'additionalMetadata' => [
                                        'description' => 'Additional data to pass to server',
                                        'type'        => 'string',
                                    ],
                                    'file'               => [
                                        'description' => 'file to upload',
                                        'type'        => 'string',
                                        'format'      => 'binary',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'responses'   => [
                    '200' => [
                        'description' => 'successful operation',
                        'content'     => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/ApiResponse',
                                ],
                            ],
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
            $this->openapiObject->getPathDefinition('/v2/pet/10/uploadImage', 'post')
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

        $this->openapiObject->getPathDefinition('/v2/pets', 'get');
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

        $this->openapiObject->getPathDefinition('/v2/pet', 'GET');
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
        $pathDefintion = $this->openapiObject->getPathDefinition('/v2/pet', 'PUT');
        $this->assertEquals(
            [
                'tags'        => [
                    'pet',
                ],
                'summary'     => 'Update an existing pet',
                'description' => '',
                'operationId' => 'updatePet',
                'requestBody' => [
                    '$ref' => '#/components/requestBodies/Pet',
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
            $pathDefintion
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

        $this->openapiObject->getDefinition('Order');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinitionFailed2(): void
    {
        $this->expectException(InvalidDefinitionException::class);

        $this->openapiObject->getDefinition('1/2/Order');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinitionFailed3(): void
    {
        $this->expectException(DefinitionNotFoundException::class);

        $this->openapiObject->getDefinition('#/components/schemas/OrderNOtFound');
    }

    /**
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function testGetDefinition(): void
    {
        $expected = [
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
        ];

        $order = $this->openapiObject->getDefinition('#/components/schemas/Order');
        $this->assertEquals($expected, $order);
    }

    /**
     * @return void
     */
    public function testGetServerUrl(): void
    {
        $this->assertEquals('http://petstore.swagger.io/v2', $this->openapiObject->getServerUrl());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testGetServerUrlVariables(): void
    {
        $this->openapiObject = new OpenApiSchema(file_get_contents(__DIR__ . '/example/openapi4.json'));

        $this->assertEquals('https://www.domain.com/api/v2', $this->openapiObject->getServerUrl());
    }

    /**
     * @return void
     * @throws JsonException
     */
    public function testGetServerUrlVariables2(): void
    {
        $this->openapiObject = new OpenApiSchema(file_get_contents(__DIR__ . '/example/openapi4.json'));
        $this->openapiObject->setServerVariable('environment', 'staging');

        $this->assertEquals('https://staging.domain.com/api/v2', $this->openapiObject->getServerUrl());
    }
}
