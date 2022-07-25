<?php

namespace ByJG\ApiTools;

use GuzzleHttp\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class ApiRequester extends AbstractRequester
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * ApiRequester constructor.
     */
    public function __construct()
    {
        $this->httpClient = new Client();

        parent::__construct();
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    protected function handleRequest(RequestInterface $request)
    {
        $request = $request->withHeader("User-Agent", "ByJG Swagger Test");

        return $this->httpClient->sendRequest($request);
    }
}
