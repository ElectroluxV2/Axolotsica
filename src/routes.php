<?php declare(strict_types=1);

use App\Actions\Account\AccountSettingsAction;
use App\Actions\GroupsAction;
use App\Actions\HomeAction;
use App\Actions\NotesAction;
use App\Actions\TestAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/', HomeAction::class)->setName('Home');

    $app->get('/groups', GroupsAction::class)->setName('Groups');

    $app->get('/notes', NotesAction::class)->setName('Notes');

    $app->get('/account/settings', AccountSettingsAction::class)->setName('Account Settings');

    $app->get('/test/[{name}]', TestAction::class)->setName('Test');

    $app->group('/users', function (Group $group) {

    });

    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
};
