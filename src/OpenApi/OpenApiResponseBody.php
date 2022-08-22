<?php

namespace GoreSakuraba\OpenAPI\OpenApi;

use GoreSakuraba\OpenAPI\Base\Body;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\InvalidRequestException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;

class OpenApiResponseBody extends Body
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
        if (empty($this->structure['content']) && !isset($this->structure['$ref'])) {
            if (!empty($body)) {
                throw new NotMatchedException("Expected empty body for '$this->name'");
            }

            return true;
        }

        if (!isset($this->structure['content']) && isset($this->structure['$ref'])) {
            $definition = $this->schema->getDefinition($this->structure['$ref']);

            return $this->matchSchema($this->name, $definition, $body);
        }

        return $this->matchSchema($this->name, $this->structure['content'][key($this->structure['content'])]['schema'], $body);
    }
}
