<?php declare(strict_types=1);

use App\Settings\Settings;
use DI\ContainerBuilder;
use Monolog\Logger;

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
                ],
                'twig' => [
                    'templatesPath' => __DIR__.'/../src/Templates',
                    'arguments' => [
                        'cache' => false //__DIR__.'/../cache/twig'
                    ]
                ]
            ]);
        }
    ]);
};
