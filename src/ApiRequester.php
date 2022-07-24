<?php

namespace ByJG\ApiTools;

use ByJG\Util\Exception\CurlException;
use ByJG\Util\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Request handler based on ByJG HttpClient (WebRequest) .
 */
class ApiRequester extends AbstractRequester
{
    /** @var HttpClient */
    private $httpClient;

    /**
     * ApiRequester constructor.
     */
    public function __construct()
    {
        $this->httpClient = HttpClient::getInstance()
            ->withNoFollowRedirect();

        parent::__construct();
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws CurlException
     */
    protected function handleRequest(RequestInterface $request)
    {
        $request = $request->withHeader("User-Agent", "ByJG Swagger Test");
        return $this->httpClient->sendRequest($request);
    }
}
