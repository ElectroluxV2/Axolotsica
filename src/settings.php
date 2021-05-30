<?php declare(strict_types=1);

use App\Settings\Settings;
use DI\ContainerBuilder;
use Medoo\Medoo;
use Minishlink\WebPush\WebPush;
use Monolog\Logger;
use Slim\Views\Twig;

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        Settings::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError' => true,
                'logErrorDetails' => false,
                'logger' => [
                    'name' => 'Axolotsica',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ], Twig::class => [
                    'templatesPath' => __DIR__.'/../src/Templates',
                    'arguments' => [
                        'cache' => false //__DIR__.'/../cache/twig'
                    ]
                ], Medoo::class => [
                    'type' => 'mysql',
                    'database' => file_get_contents(__DIR__ . '/Settings/sql_database.txt'),
                    'host' => $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ? file_get_contents(__DIR__ . '/Settings/sql_host.txt') : 'localhost',
                    'username' => file_get_contents(__DIR__ . '/Settings/sql_username.txt'),
                    'password' => file_get_contents(__DIR__ . '/Settings/sql_password.txt'),
                ], WebPush::class => [
                    'VAPID' => array(
                        'subject' => 'https://wpr.budziszm.pl/',
                        'publicKey' => file_get_contents(__DIR__ . '/Settings/vapid_public_key.txt'), // don't forget that your public key also lives in app.js
                        'privateKey' => file_get_contents(__DIR__ . '/Settings/vapid_private_key.txt'), // in the real world, this would be in a secret file
                    ),
                ]
            ]);
        }
    ]);
};
