<?php declare(strict_types=1);

use App\Actions\Account\SettingsAction;
use App\Actions\Account\SignInAction;
use App\Actions\Account\SignOutAction;
use App\Actions\Account\SignUpAction;
use App\Actions\GroupsAction;
use App\Actions\HomeAction;
use App\Actions\InstallAction;
use App\Actions\NotesAction;
use App\Actions\TestAction;
use App\Middleware\RequireAccountMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Logger;

return function (App $app) {

    $ram = new RequireAccountMiddleware($app->getResponseFactory(), $app->getContainer()->get(LoggerInterface::class));

    $app->get('/', HomeAction::class)->setName('Home')->addMiddleware($ram);

    $app->get('/groups', GroupsAction::class)->setName('Groups')->addMiddleware($ram);

    $app->get('/notes', NotesAction::class)->setName('Notes')->addMiddleware($ram);

    $app->get('/test/[{name}]', TestAction::class)->setName('Test');

    $app->get('/install', InstallAction::class)->setName('Install');

    $app->group('/account', function (Group $group) use ($ram) {
        $group->get('/settings', SettingsAction::class)->setName('Account Settings');

        $group->map(['POST', 'GET'],'/sign-in', SignInAction::class)->setName('Account Sign In');

        $group->map(['POST', 'GET'],'/sign-up', SignUpAction::class)->setName('Account Sign Up');

        $group->get('/sign-out', SignOutAction::class)->setName('Account Sign Out')->addMiddleware($ram);
    });

    /*$app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });*/
};
