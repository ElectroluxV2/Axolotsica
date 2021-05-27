<?php declare(strict_types=1);

use App\Settings\Settings;
use DI\ContainerBuilder;
use Medoo\Medoo;
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
                    'database' => 'daxkwtiqox_pjswtk',
                    'host' => $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ? 's118.linuxpl.com' : 'localhost',
                    'username' => 'daxkwtiqox_pjswtk',
                    'password' => 'Y0b#N-T6j*-1gR^m',
                ]
            ]);
        }
    ]);
};
