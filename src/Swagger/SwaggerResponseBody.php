<?php

namespace GoreSakuraba\OpenAPI\Swagger;

use GoreSakuraba\OpenAPI\Base\Body;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;

class SwaggerResponseBody extends Body
{
    /**
     * @param string $body
     *
     * @return bool
     * @throws GenericSwaggerException
     * @throws InvalidRequestException
     * @throws NotMatchedException
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function match($body): bool
    {
        if (!isset($this->structure['schema'])) {
            if (!empty($body)) {
                throw new NotMatchedException("Expected empty body for '$this->name'");
            }

            return true;
        }

        return $this->matchSchema($this->name, $this->structure['schema'], $body);
    }
}
