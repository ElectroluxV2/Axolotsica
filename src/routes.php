<?php declare(strict_types=1);

use App\Actions\HomeAction;
use App\Actions\TestAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/', HomeAction::class);

    $app->get('/test/[{name}]', TestAction::class);

    $app->group('/users', function (Group $group) {

    });

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
