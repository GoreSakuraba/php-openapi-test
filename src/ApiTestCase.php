<?php

namespace GoreSakuraba\OpenAPI;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GoreSakuraba\OpenAPI\Exception\StatusCodeNotMatchedException;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

abstract class ApiTestCase extends TestCase
{
    protected ?Schema $schema = null;
    protected ?AbstractRequester $requester = null;

    /**
     * configure the schema to use for requests
     * When set, all requests without an own schema use this one instead.
     *
     * @param Schema $schema
     *
     * @return ApiTestCase
     */
    public function setSchema(Schema $schema): ApiTestCase
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @param AbstractRequester $requester
     *
     * @return void
     */
    public function setRequester(AbstractRequester $requester): void
    {
        $this->requester = $requester;
    }

    /**
     * @return AbstractRequester
     */
    protected function getRequester(): AbstractRequester
    {
        if (is_null($this->requester)) {
            $this->requester = new ApiRequester();
        }

        return $this->requester;
    }

    /**
     * @param string     $method The HTTP Method: GET, PUT, DELETE, POST, etc
     * @param string     $path   The REST path call
     * @param int        $statusExpected
     * @param array|null $query
     * @param array|null $requestBody
     * @param array      $requestHeader
     *
     * @return ResponseInterface
     * @throws DefinitionNotFoundException
     * @throws InvalidRequestException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws StatusCodeNotMatchedException
     * @throws JsonException
     * @deprecated Use assertRequest instead
     */
    protected function makeRequest(
        string $method,
        string $path,
        int $statusExpected = 200,
        ?array $query = null,
        ?array $requestBody = null,
        array $requestHeader = []
    ): ResponseInterface {
        $this->checkSchema();
        $body = $this->requester
            ->withSchema($this->schema)
            ->withMethod($method)
            ->withPath($path)
            ->withQuery($query)
            ->withRequestBody($requestBody)
            ->withRequestHeader($requestHeader)
            ->assertResponseCode($statusExpected)
            ->send();

        // Note:
        // This code is only reached if the 'send' is successful and all matches are satisfied.
        // Otherwise, an error is thrown before reaching this.
        $this->assertTrue(true);

        return $body;
    }

    /**
     * @param AbstractRequester $request
     *
     * @return ResponseInterface
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
    public function assertRequest(AbstractRequester $request): ResponseInterface
    {
        // Add own schema if nothing is passed.
        if (!$request->hasSchema()) {
            $this->checkSchema();
            $request = $request->withSchema($this->schema);
        }

        // Request based on the Swagger Request definitions
        $body = $request->send();

        // Note:
        // This code is only reached if the 'send' is successful and all matches are satisfied.
        // Otherwise, an error is thrown before reaching this.
        $this->assertTrue(true);

        return $body;
    }

    /**
     * @throws GenericSwaggerException
     */
    protected function checkSchema(): void
    {
        if (!$this->schema) {
            throw new GenericSwaggerException('You have to configure a schema for either the request or the testcase');
        }
    }
}
