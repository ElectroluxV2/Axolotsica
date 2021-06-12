<?php declare(strict_types=1);

use App\Actions\Account\SettingsAction;
use App\Actions\Account\SignInAction;
use App\Actions\Account\SignOutAction;
use App\Actions\Account\SignOutPermanentlyAction;
use App\Actions\Account\SignUpAction;
use App\Actions\Account\SubscribeAction;
use App\Actions\Groups\GroupsCreateAction;
use App\Actions\Groups\GroupsDeleteAction;
use App\Actions\Groups\GroupsJoinAction;
use App\Actions\Groups\GroupsListAction;
use App\Actions\Groups\GroupsRemoveNoteAction;
use App\Actions\Groups\GroupsRemoveUserAction;
use App\Actions\Groups\GroupsSettingsAction;
use App\Actions\Groups\GroupsShareAction;
use App\Actions\Groups\GroupsViewAction;
use App\Actions\HomeAction;
use App\Actions\InstallAction;
use App\Actions\Notes\NotesCreateAction;
use App\Actions\Notes\NotesDeleteAction;
use App\Actions\Notes\NotesEditAction;
use App\Actions\Notes\NotesListAction;
use App\Actions\Notes\NotesShareAction;
use App\Actions\Notes\NotesViewAction;
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

        $groups->map(['POST', 'GET'],'/delete/{group_id}/{group_name}', GroupsDeleteAction::class)->setName('Groups Delete');

        $groups->map(['POST', 'GET'],'/share/{group_id}/{group_name}', GroupsShareAction::class)->setName('Groups Share');

        $groups->map(['POST', 'GET'],'/settings/{group_id}/{group_name}', GroupsSettingsAction::class)->setName('Groups Settings');

        $groups->map(['POST', 'GET'],'/remove-user/{group_id}/{user_id}', GroupsRemoveUserAction::class)->setName('Groups Remove User');

        $groups->map(['POST', 'GET'],'/remove-note/{group_id}/{note_id}', GroupsRemoveNoteAction::class)->setName('Groups Remove Note');

        $groups->get('/view/{group_id}/{group_name}', GroupsViewAction::class)->setName('Groups View');

        $groups->get('/join/{group_id}/{hash}', GroupsJoinAction::class)->setName('Groups Join');

    })->addMiddleware($ram);

    $app->group('/notes', function (Group $notes) {
        $notes->get('', NotesListAction::class)->setName('Notes List');

        $notes->map(['POST', 'GET'],'/create', NotesCreateAction::class)->setName('Notes Create');

        $notes->map(['POST', 'GET'],'/delete/{note_id}/{note_name}', NotesDeleteAction::class)->setName('Notes Delete');

        $notes->map(['POST', 'GET'],'/edit/{note_id}/{note_name}', NotesEditAction::class)->setName('Notes Edit');

        $notes->map(['POST', 'GET'],'/share/{note_id}/{note_name}', NotesShareAction::class)->setName('Notes Share');

        $notes->get('/view/{note_id}/{note_name}', NotesViewAction::class)->setName('Notes View');

    })->addMiddleware($ram);

    $app->get('/test/[{name}]', TestAction::class)->setName('Test');

    $app->get('/install', InstallAction::class)->setName('Install');

    $app->group('/account', function (Group $account) use ($ram) {
        $account->map(['POST', 'GET'],'/settings', SettingsAction::class)->setName('Account Settings')->addMiddleware($ram);;

        $account->map(['POST', 'GET'],'/sign-in', SignInAction::class)->setName('Account Sign In');

        $account->map(['POST', 'GET'],'/sign-up', SignUpAction::class)->setName('Account Sign Up');

        $account->get('/sign-out', SignOutAction::class)->setName('Account Sign Out')->addMiddleware($ram);

        $account->get('/sign-out-permanently', SignOutPermanentlyAction::class)->setName('Account Sign Out Permanently')->addMiddleware($ram);

        $account->post('/subscribe', SubscribeAction::class)->setName('Subscribe')->addMiddleware($ram);
    });

    /*$app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });*/
};
