<?php

namespace Test\Rest;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Test\Rest\Classes\Handler;

require dirname(__DIR__, 2) . '/vendor/autoload.php';
require_once __DIR__ . '/classes/Handler.php';
require_once __DIR__ . '/classes/Pet.php';
require_once __DIR__ . '/classes/Category.php';
require_once __DIR__ . '/classes/Tag.php';

$handler = new Handler();

$app = AppFactory::create();

$app->group('/v2', function (RouteCollectorProxy $group) use ($handler): void {
    $group->post('/pet', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($handler): ResponseInterface {
        return $handler->addPet($request, $response, $args);
    });

    $group->get('/pet/{id}', function (ServerRequestInterface $request, ResponseInterface $response, array $args) use ($handler): ResponseInterface {
        return $handler->getPetById($request, $response, $args);
    });

    $group->post('/inventory', function (ServerRequestInterface $request, ResponseInterface $response, $args) use ($handler): ResponseInterface {
        return $handler->processUpload($request, $response, $args);
    });
});

$app->run();
