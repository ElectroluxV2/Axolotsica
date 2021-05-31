<?php declare(strict_types=1);

use App\Actions\Account\SettingsAction;
use App\Actions\Account\SignInAction;
use App\Actions\Account\SignOutAction;
use App\Actions\Account\SignUpAction;
use App\Actions\Groups\GroupsCreateAction;
use App\Actions\Groups\GroupsDeleteAction;
use App\Actions\Groups\GroupsListAction;
use App\Actions\Groups\GroupsSettingsAction;
use App\Actions\Groups\GroupsViewAction;
use App\Actions\HomeAction;
use App\Actions\InstallAction;
use App\Actions\Notes\NotesCreateAction;
use App\Actions\Notes\NotesDeleteAction;
use App\Actions\Notes\NotesListAction;
use App\Actions\TestAction;
use App\Middleware\RequireAccountMiddleware;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $ram = new RequireAccountMiddleware($app->getResponseFactory(), $app->getContainer()->get(LoggerInterface::class));

    $app->get('/', HomeAction::class)->setName('Home')->addMiddleware($ram);

    $app->group('/groups', function (Group $groups) {
        $groups->get('', GroupsListAction::class)->setName('Groups List');

        $groups->map(['POST', 'GET'],'/create', GroupsCreateAction::class)->setName('Groups Create');

        $groups->post('/delete', GroupsDeleteAction::class)->setName('Groups Delete');

        $groups->map(['POST', 'GET'], '/settings', GroupsSettingsAction::class)->setName('Groups Settings');

        $groups->get('/view/{group_id}/{group_name}', GroupsViewAction::class)->setName('Groups View');

    })->addMiddleware($ram);

    $app->group('/notes', function (Group $notes) {
        $notes->get('', NotesListAction::class)->setName('Notes List');

        $notes->map(['POST', 'GET'],'/create', NotesCreateAction::class)->setName('Notes Create');

        $notes->post('/delete', NotesDeleteAction::class)->setName('Notes Delete');

    })->addMiddleware($ram);

    $app->get('/test/[{name}]', TestAction::class)->setName('Test');

    $app->get('/install', InstallAction::class)->setName('Install');

    $app->group('/account', function (Group $account) use ($ram) {
        $account->get('/settings', SettingsAction::class)->setName('Account Settings');

        $account->map(['POST', 'GET'],'/sign-in', SignInAction::class)->setName('Account Sign In');

        $account->map(['POST', 'GET'],'/sign-up', SignUpAction::class)->setName('Account Sign Up');

        $account->get('/sign-out', SignOutAction::class)->setName('Account Sign Out')->addMiddleware($ram);
    });

    /*$app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });*/
};
