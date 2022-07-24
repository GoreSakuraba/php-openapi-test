<?php

namespace ByJG\Util;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MockClient extends HttpClient
{
    /**
     * @var ResponseInterface
     */
    protected $expectedResponse;

    /**
     * MockClient constructor.
     * @param ResponseInterface|null $expectedResponse
     */
    public function __construct(?ResponseInterface $expectedResponse = null)
    {
        $this->expectedResponse = $expectedResponse ?? new Response(200, [], Utils::streamFor('{"key":"value"}'));
    }

    /**
     * @return MockClient
     */
    public static function getInstance()
    {
        return new MockClient(new Response());
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     * @throws Exception\CurlException
     */
    public function sendRequest(RequestInterface $request)
    {
        $curlHandle = $this->createCurlHandle($request);

        return $this->parseCurl("", $curlHandle);
    }

    /**
     * @param $body
     * @param $curlHandle
     * @param $close
     *
     * @return ResponseInterface
     */
    public function parseCurl($body, $curlHandle, $close = true)
    {
        return $this->expectedResponse;
    }

    /**
     * @param RequestInterface $request
     * @return resource
     * @throws Exception\CurlException
     */
    public function createCurlHandle(RequestInterface $request)
    {
        $this->request = clone $request;
        $this->curlOptions = [];
        $this->clearRequestMethod();
        $this->defaultCurlOptions();

        $this->setCredentials();
        $this->setHeaders();
        $this->setMethod();
        $this->setBody();

        return $this->curlInit();
    }



    /**
     * Request the method using the CURLOPT defined previously;
     *
     * @return resource
     */
    protected function curlInit()
    {
        return 65535;
    }

    /**
     * @return array
     */
    public function getCurlConfiguration()
    {
        return $this->curlOptions;
    }

    /**
     * @return RequestInterface
     */
    public function getRequestedObject()
    {
        return $this->request;
    }

    /**
     * @return ResponseInterface
     */
    public function getExpectedResponse()
    {
        return $this->expectedResponse;
    }
}
