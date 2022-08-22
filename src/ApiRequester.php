<?php

namespace ByJG\ApiTools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class ApiRequester extends AbstractRequester
{
    private Client $httpClient;

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
     * @throws GuzzleException
     */
    protected function handleRequest(RequestInterface $request): ResponseInterface
    {
        $request = $request->withHeader('User-Agent', 'ByJG Swagger Test');

        return $this->httpClient->send($request, [
            RequestOptions::SYNCHRONOUS     => true,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS     => false,
        ]);
    }
}
