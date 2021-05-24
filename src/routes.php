<?php declare(strict_types=1);

use App\Actions\Account\SettingsAction;
use App\Actions\Account\SignInAction;
use App\Actions\Account\SignOutAction;
use App\Actions\Account\SignUpAction;
use App\Actions\GroupsAction;
use App\Actions\HomeAction;
use App\Actions\NotesAction;
use App\Actions\TestAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->get('/', HomeAction::class)->setName('Home');

    $app->get('/groups', GroupsAction::class)->setName('Groups');

    $app->get('/notes', NotesAction::class)->setName('Notes');

    $app->get('/test/[{name}]', TestAction::class)->setName('Test');

    $app->group('/account', function (Group $group) {
        $group->get('/settings', SettingsAction::class)->setName('Account Settings');

        $group->get('/sign-in', SignInAction::class)->setName('Account Sign In');

        $group->get('/sign-up', SignUpAction::class)->setName('Account Sign Up');

        $group->get('/sign-out', SignOutAction::class)->setName('Account Sign Out');
    });

    /*$app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });*/
};
