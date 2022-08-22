<?php

namespace GoreSakuraba\OpenAPI\Base;

use GoreSakuraba\OpenAPI\Exception\DefinitionNotFoundException;
use GoreSakuraba\OpenAPI\Exception\GenericSwaggerException;
use GoreSakuraba\OpenAPI\Exception\HttpMethodNotFoundException;
use GoreSakuraba\OpenAPI\Exception\InvalidDefinitionException;
use GoreSakuraba\OpenAPI\Exception\NotMatchedException;
use GoreSakuraba\OpenAPI\Exception\PathNotFoundException;
use GoreSakuraba\OpenAPI\OpenApi\OpenApiSchema;
use GoreSakuraba\OpenAPI\Swagger\SwaggerSchema;
use GuzzleHttp\Psr7\Uri;
use InvalidArgumentException;
use JsonException;

abstract class Schema
{
    /**
     * @var array
     */
    protected array $jsonFile;
    protected bool $allowNullValues = false;
    protected string $specificationVersion;
    protected const SWAGGER_PATHS = 'paths';
    protected const SWAGGER_PARAMETERS = 'parameters';
    protected const SWAGGER_COMPONENTS = 'components';

    /**
     * Returns the major specification version
     *
     * @return string
     */
    public function getSpecificationVersion(): string
    {
        return $this->specificationVersion;
    }

    /**
     * Factory function for schemata.
     * Initialize with schema data, which can be a PHP array or encoded as JSON.
     * This determines the type of the schema from the given data.
     *
     * @param array|string $data
     * @param bool         $extraArgs
     *
     * @return Schema
     * @throws JsonException
     */
    public static function getInstance($data, bool $extraArgs = false): Schema
    {
        // when given a string, decode from JSON
        if (is_string($data)) {
            $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        }
        // make sure we got an array
        if (!is_array($data)) {
            throw new InvalidArgumentException('schema must be given as array or JSON string');
        }
        // check which type of file we got and dispatch to derived class constructor
        if (isset($data['swagger'])) {
            return new SwaggerSchema($data, $extraArgs);
        }
        if (isset($data['openapi'])) {
            return new OpenApiSchema($data);
        }

        throw new InvalidArgumentException('failed to determine schema type from data');
    }

    /**
     * @param string $path
     * @param string $method
     *
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     */
    public function getPathDefinition(string $path, string $method)
    {
        $method = strtolower($method);

        $path = preg_replace('~^' . $this->getBasePath() . '~', '', $path);

        $uri = new Uri($path);

        // Try direct match
        if (isset($this->jsonFile[self::SWAGGER_PATHS][$uri->getPath()])) {
            if (isset($this->jsonFile[self::SWAGGER_PATHS][$uri->getPath()][$method])) {
                return $this->jsonFile[self::SWAGGER_PATHS][$uri->getPath()][$method];
            }
            throw new HttpMethodNotFoundException("The http method '$method' not found in '$path'");
        }

        // Try inline parameter
        foreach (array_keys($this->jsonFile[self::SWAGGER_PATHS]) as $pathItem) {
            if (strpos($pathItem, '{') === false) {
                continue;
            }

            $pathItemPattern = '~^' . preg_replace('~{(.*?)}~', '(?<\1>[^/]+)', $pathItem) . '$~';

            $matches = [];
            if (preg_match($pathItemPattern, $uri->getPath(), $matches)) {
                $pathDef = $this->jsonFile[self::SWAGGER_PATHS][$pathItem];
                if (!isset($pathDef[$method])) {
                    throw new HttpMethodNotFoundException("The http method '$method' not found in '$path'");
                }

                $parametersPathMethod = [];
                $parametersPath = [];

                if (isset($pathDef[$method][self::SWAGGER_PARAMETERS])) {
                    $parametersPathMethod = $pathDef[$method][self::SWAGGER_PARAMETERS];
                }

                if (isset($pathDef[self::SWAGGER_PARAMETERS])) {
                    $parametersPath = $pathDef[self::SWAGGER_PARAMETERS];
                }

                $this->validateArguments('path', array_merge($parametersPathMethod, $parametersPath), $matches);

                return $pathDef[$method];
            }
        }

        throw new PathNotFoundException("Path '$path' not found");
    }

    /**
     * @param string $path
     * @param string $method
     * @param int    $status
     *
     * @return Body
     * @throws DefinitionNotFoundException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     * @throws PathNotFoundException
     * @throws GenericSwaggerException
     */
    public function getResponseParameters(string $path, string $method, int $status): Body
    {
        $structure = $this->getPathDefinition($path, $method);

        if (!isset($structure['responses']['200'])) {
            $structure['responses']['200'] = ['description' => 'Auto Generated OK'];
        }

        $verifyStatus = $status;
        if (!isset($structure['responses'][$verifyStatus])) {
            $verifyStatus = 'default';
            if (!isset($structure['responses'][$verifyStatus])) {
                throw new InvalidDefinitionException("Could not find status code '$status' in '$path' and '$method'");
            }
        }

        return Body::getInstance($this, "$method $status $path", $structure['responses'][$verifyStatus]);
    }

    /**
     * OpenApi 2.0 doesn't describe null values, so this flag defines,
     * if match is ok when one of property
     *
     * @return bool
     */
    public function isAllowNullValues(): bool
    {
        return $this->allowNullValues;
    }

    /**
     * @return mixed
     */
    abstract public function getServerUrl();

    /**
     * @param mixed $parameterIn
     * @param array $parameters
     * @param array $arguments
     *
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     * @throws NotMatchedException
     */
    abstract protected function validateArguments($parameterIn, array $parameters, array $arguments);

    /**
     * @return mixed
     */
    abstract public function getBasePath();

    /**
     * @param string $name
     *
     * @return mixed
     * @throws DefinitionNotFoundException
     * @throws InvalidDefinitionException
     */
    abstract public function getDefinition(string $name);

    /**
     * @param string $path
     * @param string $method
     *
     * @return Body
     * @throws HttpMethodNotFoundException
     * @throws PathNotFoundException
     */
    abstract public function getRequestParameters(string $path, string $method): Body;
}
