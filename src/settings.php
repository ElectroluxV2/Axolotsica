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
                'logError' => false,
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
                    'host' => 'localhost',
                    'database' => 'name',
                    'username' => 'your_username',
                    'password' => 'your_password',
                ]
            ]);
        }
    ]);
};
