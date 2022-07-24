<?php


namespace ByJG\ApiTools;

use ByJG\Util\Exception\CurlException;
use ByJG\Util\MockClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockRequester extends AbstractRequester
{
    /** @var MockClient */
    private $httpClient;

    /**
     * MockAbstractRequest constructor.
     * @param ResponseInterface $expectedResponse
     */
    public function __construct(ResponseInterface $expectedResponse)
    {
        $this->httpClient = new MockClient($expectedResponse);
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
