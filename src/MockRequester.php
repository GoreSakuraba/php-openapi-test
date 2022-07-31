<?php


namespace ByJG\ApiTools;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockRequester extends AbstractRequester
{
    /**
     * @var Client
     */
    private $httpClient;

    /**
     * MockAbstractRequest constructor.
     * @param ResponseInterface $expectedResponse
     */
    public function __construct(ResponseInterface $expectedResponse)
    {
        $mockHandler = new MockHandler([$expectedResponse]);
        $handlerStack = HandlerStack::create($mockHandler);
        $this->httpClient = new Client(['handler' => $handlerStack]);

        parent::__construct();
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function handleRequest(RequestInterface $request)
    {
        $request = $request->withHeader("User-Agent", "ByJG Swagger Test");

        return $this->httpClient->send($request, [
            RequestOptions::SYNCHRONOUS     => true,
            RequestOptions::ALLOW_REDIRECTS => false,
            RequestOptions::HTTP_ERRORS     => false,
        ]);
    }
}
