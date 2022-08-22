<?php

namespace GoreSakuraba\OpenAPI\Swagger;

use GoreSakuraba\OpenAPI\Base\Schema;
use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use InvalidArgumentException;
use JsonException;

class SwaggerSchema extends Schema
{
    /**
     * Initialize with schema data, which can be a PHP array or encoded as JSON.
     *
     * @param array|string $data
     * @param bool         $allowNullValues
     *
     * @throws JsonException
     */
    public function __construct($data, bool $allowNullValues = false)
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
        $this->allowNullValues = $allowNullValues;
    }

    /**
     * @return mixed|string
     */
    public function getHttpSchema()
    {
        return isset($this->jsonFile['schemes']) ? $this->jsonFile['schemes'][0] : '';
    }

    /**
     * @return mixed|string
     */
    public function getHost()
    {
        return $this->jsonFile['host'] ?? '';
    }

    /**
     * @return mixed|string
     */
    public function getBasePath()
    {
        return $this->jsonFile['basePath'] ?? '';
    }

    /**
     * @return string
     */
    public function getServerUrl(): string
    {
        $httpSchema = $this->getHttpSchema();
        if (!empty($httpSchema)) {
            $httpSchema .= '://';
        }
        $host = $this->getHost();
        $basePath = $this->getBasePath();

        return "$httpSchema$host$basePath";
    }

    /**
     * @param mixed $parameterIn
     * @param array $parameters
     * @param array $arguments
     *
     * @return void
     * @throws NotMatchedException
     */
    protected function validateArguments($parameterIn, array $parameters, array $arguments): void
    {
        foreach ($parameters as $parameter) {
            if ($parameter['in'] === $parameterIn
                && $parameter['type'] === 'integer'
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

        if (count($nameParts) < 3 || $nameParts[0] !== '#') {
            throw new InvalidDefinitionException('Invalid Definition');
        }

        if (!isset($this->jsonFile[$nameParts[1]][$nameParts[2]])) {
            throw new DefinitionNotFoundException("Definition '$name' not found");
        }

        return $this->jsonFile[$nameParts[1]][$nameParts[2]];
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return SwaggerRequestBody
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function getRequestParameters(string $path, string $method): SwaggerRequestBody
    {
        $structure = $this->getPathDefinition($path, $method);

        if (!isset($structure[self::SWAGGER_PARAMETERS])) {
            return new SwaggerRequestBody($this, "$method $path", []);
        }

        return new SwaggerRequestBody($this, "$method $path", $structure[self::SWAGGER_PARAMETERS]);
    }

    /**
     * OpenApi 2.0 doesn't describe null values, so this flag defines,
     * if match is ok when one of property
     *
     * @param bool $value
     *
     * @return SwaggerSchema
     */
    public function setAllowNullValues(bool $value): SwaggerSchema
    {
        $this->allowNullValues = $value;

        return $this;
    }
}
