<?php

namespace GoreSakuraba\OpenAPI;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\StatusCodeNotMatchedException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Abstract baseclass for request handlers.
 * The baseclass provides processing and verification of request and response.
 * It only delegates the actual message exchange to the derived class. For the
 * messages, it uses the PSR-7 implementation from Guzzle.
 * This is an implementation of the Template Method Pattern
 * (https://en.wikipedia.org/wiki/Template_method_pattern).
 */
abstract class AbstractRequester
{
    protected ?Schema $schema = null;
    protected int $statusExpected = 200;
    /**
     * @var array
     */
    protected array $assertHeader = [];
    /**
     * @var array
     */
    protected array $assertBody = [];
    protected RequestInterface $psr7Request;

    /**
     * AbstractRequester constructor.
     */
    public function __construct()
    {
        $this->withPsr7Request(new Request('get', '/'));
    }

    /**
     * abstract function to be implemented by derived classes
     * This function must be implemented by derived classes. It should process
     * the given request and return an according response.
     *
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    abstract protected function handleRequest(RequestInterface $request): ResponseInterface;

    /**
     * @param Schema $schema
     *
     * @return AbstractRequester
     */
    public function withSchema(Schema $schema): AbstractRequester
    {
        $this->schema = $schema;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSchema(): bool
    {
        return !empty($this->schema);
    }

    /**
     * @param string $method
     *
     * @return AbstractRequester
     */
    public function withMethod(string $method): AbstractRequester
    {
        $this->psr7Request = $this->psr7Request->withMethod($method);

        return $this;
    }

    /**
     * @param string $path
     *
     * @return AbstractRequester
     */
    public function withPath(string $path): AbstractRequester
    {
        $uri = $this->psr7Request->getUri()->withPath($path);
        $this->psr7Request = $this->psr7Request->withUri($uri);

        return $this;
    }

    /**
     * @param array $requestHeader
     *
     * @return AbstractRequester
     */
    public function withRequestHeader(array $requestHeader): AbstractRequester
    {
        foreach ($requestHeader as $name => $value) {
            $this->psr7Request = $this->psr7Request->withHeader($name, $value);
        }

        return $this;
    }

    /**
     * @param array|null $query
     *
     * @return AbstractRequester
     */
    public function withQuery(?array $query = null): AbstractRequester
    {
        $uri = $this->psr7Request->getUri();

        if (is_null($query)) {
            $uri = $uri->withQuery('');
            $this->psr7Request = $this->psr7Request->withUri($uri);

            return $this;
        }

        $currentQuery = [];
        parse_str($uri->getQuery(), $currentQuery);

        $uri = $uri->withQuery(http_build_query(array_merge($currentQuery, $query)));
        $this->psr7Request = $this->psr7Request->withUri($uri);

        return $this;
    }

    /**
     * @param mixed $requestBody
     *
     * @return AbstractRequester
     * @throws JsonException
     */
    public function withRequestBody($requestBody): AbstractRequester
    {
        $contentType = $this->psr7Request->getHeaderLine('Content-Type');
        if (is_array($requestBody) && (empty($contentType) || strpos($contentType, 'application/json') !== false)) {
            $requestBody = json_encode($requestBody, JSON_THROW_ON_ERROR);
        }
        $this->psr7Request = $this->psr7Request->withBody(Utils::streamFor($requestBody));

        return $this;
    }

    /**
     * @param RequestInterface $requestInterface
     *
     * @return AbstractRequester
     */
    public function withPsr7Request(RequestInterface $requestInterface): AbstractRequester
    {
        $this->psr7Request = $requestInterface->withHeader('Accept', 'application/json');

        return $this;
    }

    /**
     * @param int $code
     *
     * @return AbstractRequester
     */
    public function assertResponseCode(int $code): AbstractRequester
    {
        $this->statusExpected = $code;

        return $this;
    }

    /**
     * @param string $header
     * @param mixed  $contains
     *
     * @return AbstractRequester
     */
    public function assertHeaderContains(string $header, $contains): AbstractRequester
    {
        $this->assertHeader[$header] = $contains;

        return $this;
    }

    /**
     * @param mixed $contains
     *
     * @return AbstractRequester
     */
    public function assertBodyContains($contains): AbstractRequester
    {
        $this->assertBody[] = $contains;

        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws Exception\DefinitionNotFoundException
     * @throws Exception\GenericSwaggerException
     * @throws Exception\HttpMethodNotFoundException
     * @throws Exception\InvalidDefinitionException
     * @throws Exception\PathNotFoundException
     * @throws NotMatchedException
     * @throws StatusCodeNotMatchedException
     * @throws InvalidRequestException
     * @throws JsonException
     */
    public function send(): ResponseInterface
    {
        // Process URI based on the OpenAPI schema
        $uriSchema = new Uri($this->schema->getServerUrl());

        if (empty($uriSchema->getScheme())) {
            $uriSchema = $uriSchema->withScheme($this->psr7Request->getUri()->getScheme());
        }

        if (empty($uriSchema->getHost())) {
            $uriSchema = $uriSchema->withHost($this->psr7Request->getUri()->getHost());
        }

        $uri = $this->psr7Request->getUri()
                                 ->withScheme($uriSchema->getScheme())
                                 ->withHost($uriSchema->getHost())
                                 ->withPort($uriSchema->getPort())
                                 ->withPath($uriSchema->getPath() . $this->psr7Request->getUri()->getPath());

        if (!preg_match("~^{$this->schema->getBasePath()}~", $uri->getPath())) {
            $uri = $uri->withPath($this->schema->getBasePath() . $uri->getPath());
        }

        $this->psr7Request = $this->psr7Request->withUri($uri);

        // Prepare Body to Match Against Specification
        $requestBody = $this->psr7Request->getBody();
        if ($requestBody !== null) {
            $requestBody = $requestBody->getContents();

            $contentType = $this->psr7Request->getHeaderLine('content-type');
            if (empty($contentType) || strpos($contentType, 'application/json') !== false) {
                if ($requestBody === '') {
                    $requestBody = [];
                } else {
                    $requestBody = json_decode($requestBody, true, 512, JSON_THROW_ON_ERROR);
                }
            } elseif (strpos($contentType, 'multipart/') !== false) {
                $requestBody = $this->parseMultiPartForm($contentType, $requestBody);
            } else {
                throw new InvalidRequestException("Cannot handle Content Type '$contentType'");
            }
        }

        // Check if the body is the expected before request
        $bodyRequestDef = $this->schema->getRequestParameters($this->psr7Request->getUri()->getPath(), $this->psr7Request->getMethod());
        $bodyRequestDef->match($requestBody);

        // Handle Request
        $response = $this->handleRequest($this->psr7Request);
        $responseHeader = $response->getHeaders();
        $responseBodyStr = (string)$response->getBody();
        if ($responseBodyStr === '') {
            $responseBody = [];
        } else {
            $responseBody = json_decode($responseBodyStr, true, 512, JSON_THROW_ON_ERROR);
        }
        $statusReturned = $response->getStatusCode();

        // Assert results
        if ($this->statusExpected !== $statusReturned) {
            throw new StatusCodeNotMatchedException(
                "Status code not matched: Expected '$this->statusExpected', got '$statusReturned'",
                $responseBody
            );
        }

        $bodyResponseDef = $this->schema->getResponseParameters(
            $this->psr7Request->getUri()->getPath(),
            $this->psr7Request->getMethod(),
            $this->statusExpected
        );
        $bodyResponseDef->match($responseBody);

        foreach ($this->assertHeader as $key => $value) {
            if (!isset($responseHeader[$key]) || strpos($responseHeader[$key][0], $value) === false) {
                throw new NotMatchedException(
                    "Header '$key' does not contain value '$value'",
                    $responseHeader
                );
            }
        }

        if (!empty($responseBodyStr)) {
            foreach ($this->assertBody as $item) {
                if (strpos($responseBodyStr, $item) === false) {
                    throw new NotMatchedException("Body does not contain '$item'");
                }
            }
        }

        return $response;
    }

    /**
     * @param string $contentType
     * @param mixed  $body
     *
     * @return array|null
     */
    protected function parseMultiPartForm(string $contentType, $body): ?array
    {
        $matchRequest = [];

        if ($contentType === '' || strpos($contentType, 'multipart/') === false) {
            return null;
        }

        $matches = [];

        preg_match('/^--(.*)/', $body, $matches);
        $boundary = $matches[1];

        // split content by boundary and get rid of last -- element
        $blocks = preg_split("/-+$boundary/", rtrim($body, "--$boundary--\n"), -1, PREG_SPLIT_NO_EMPTY);

        // loop data blocks
        foreach ($blocks as $block) {
            $block = trim($block);

            preg_match('/\bname=\"([^\"]*)\"/', $block, $matches);
            $name = $matches[1];

            $matches = [];
            preg_match('/^\s*$/m', $block, $matches, PREG_OFFSET_CAPTURE);

            $matchRequest[$name] = trim(substr($block, $matches[0][1]));
        }

        return $matchRequest;
    }
}
