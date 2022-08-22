<?php

namespace GoreSakuraba\OpenAPI\Exception;

use Exception;
use JsonException;
use Throwable;

class BaseException extends Exception
{
    /**
     * @var array|string
     */
    protected $body;

    /**
     * @param string                      $message
     * @param array<string, mixed>|string $body
     * @param int                         $code
     * @param Throwable|null              $previous
     *
     * @throws JsonException
     */
    public function __construct(string $message = "", $body = [], int $code = 0, Throwable $previous = null)
    {
        $this->body = $body;
        if (!empty($body)) {
            $message .= " ->\n" . json_encode($body, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . "\n";
        }
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }
}
