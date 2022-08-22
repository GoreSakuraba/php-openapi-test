<?php

namespace GoreSakuraba\OpenAPI\OpenApi;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use JsonException;

class OpenApiSchema extends Schema
{
    protected array $serverVariables = [];

    /**
     * Initialize with schema data, which can be a PHP array or encoded as JSON.
     *
     * @param array|string $data
     *
     * @throws JsonException
     */
    public function __construct($data)
    {
        // when given a string, decode from JSON
        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }
        // make sure we got an array
        if (!is_array($data)) {
            throw new InvalidArgumentException('schema must be given as array or JSON string');
        }
        $this->jsonFile = $data;
    }

    /**
     * @return array|mixed|string|string[]|null
     */
    public function getServerUrl()
    {
        if (!isset($this->jsonFile['servers'])) {
            return '';
        }
        $serverUrl = $this->jsonFile['servers'][0]['url'];

        if (isset($this->jsonFile['servers'][0]['variables'])) {
            foreach ($this->jsonFile['servers'][0]['variables'] as $var => $value) {
                if (!isset($this->serverVariables[$var])) {
                    $this->serverVariables[$var] = $value['default'];
                }
            }
        }

        foreach ($this->serverVariables as $var => $value) {
            $serverUrl = preg_replace("/\{$var}/", $value, $serverUrl);
        }

        return $serverUrl;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return (new Uri($this->getServerUrl()))->getPath();
    }

    /**
     * @param mixed $parameterIn
     * @param array $parameters
     * @param array $arguments
     *
     * @return void
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     */
    protected function validateArguments($parameterIn, array $parameters, array $arguments): void
    {
        foreach ($parameters as $parameter) {
            if (isset($parameter['$ref'])) {
                $paramParts = explode('/', $parameter['$ref']);
                if (count($paramParts) !== 4 || $paramParts[0] !== '#' || $paramParts[1] !== self::SWAGGER_COMPONENTS || $paramParts[2] !== self::SWAGGER_PARAMETERS) {
                    throw new InvalidDefinitionException(
                        'Not get the reference in the expected format #/components/parameters/<NAME>'
                    );
                }
                if (!isset($this->jsonFile[self::SWAGGER_COMPONENTS][self::SWAGGER_PARAMETERS][$paramParts[3]])) {
                    throw new DefinitionNotFoundException(
                        "Not find reference #/components/parameters/$paramParts[3]"
                    );
                }
                $parameter = $this->jsonFile[self::SWAGGER_COMPONENTS][self::SWAGGER_PARAMETERS][$paramParts[3]];
            }
            if ($parameter['in'] === $parameterIn
                && $parameter['schema']['type'] === 'integer'
                && filter_var($arguments[$parameter['name']], FILTER_VALIDATE_INT) === false) {
                throw new NotMatchedException('Path expected an integer value');
            }
        }
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    public function getDefinition(string $name)
    {
        $nameParts = explode('/', $name);

        if (count($nameParts) < 4 || $nameParts[0] !== '#') {
            throw new InvalidDefinitionException('Invalid Component');
        }

        if (!isset($this->jsonFile[$nameParts[1]][$nameParts[2]][$nameParts[3]])) {
            throw new DefinitionNotFoundException("Component '$name' not found");
        }

        return $this->jsonFile[$nameParts[1]][$nameParts[2]][$nameParts[3]];
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return OpenApiRequestBody
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function getRequestParameters(string $path, string $method): OpenApiRequestBody
    {
        $structure = $this->getPathDefinition($path, $method);

        if (!isset($structure['requestBody'])) {
            return new OpenApiRequestBody($this, "$method $path", []);
        }

        return new OpenApiRequestBody($this, "$method $path", $structure['requestBody']);
    }

    /**
     * @param string $var
     * @param mixed  $value
     *
     * @return OpenApiSchema
     */
    public function setServerVariable(string $var, $value): OpenApiSchema
    {
        $this->serverVariables[$var] = $value;

        return $this;
    }
}
