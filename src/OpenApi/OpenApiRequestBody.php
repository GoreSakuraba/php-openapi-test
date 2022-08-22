<?php

namespace GoreSakuraba\OpenAPI\OpenApi;

use GoreSakuraba\OpenAPI\Base\Body;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\RequiredArgumentNotFound;

class OpenApiRequestBody extends Body
{
    /**
     * @param array|string $body
     *
     * @return bool
     * @throws GenericSwaggerException
     * @throws InvalidDefinitionException
     * @throws InvalidRequestException
     * @throws NotMatchedException
     * @throws RequiredArgumentNotFound
     * @throws DefinitionNotFoundException
     */
    public function match($body): bool
    {
        if (isset($this->structure['content']) || isset($this->structure['$ref'])) {
            if (isset($this->structure['required']) && $this->structure['required'] === true && empty($body)) {
                throw new RequiredArgumentNotFound('The body is required but it is empty');
            }

            if (isset($this->structure['$ref'])) {
                return $this->matchSchema($this->name, $this->structure, $body);
            }

            return $this->matchSchema($this->name, $this->structure['content'][key($this->structure['content'])]['schema'], $body);
        }

        if (!empty($body)) {
            throw new InvalidDefinitionException('Body is passed but there is no request body definition');
        }

        return false;
    }
}
