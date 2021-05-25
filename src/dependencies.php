<?php declare(strict_types=1);

use App\Settings\Settings;
use DI\ContainerBuilder;
use Medoo\Medoo;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(Settings::class);

            $loggerSettings = $settings->get("logger");
            $logger = new Logger($loggerSettings["name"]);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings["path"], $loggerSettings["level"]);
            $logger->pushHandler($handler);

            return $logger;
        }, Twig::class => function (ContainerInterface $c) {
            $settings = $c->get(Settings::class);
            $twigSettings = $settings->get(Twig::class);
            return Twig::create($twigSettings["templatesPath"], $twigSettings["arguments"]);
        }, Medoo::class => function (ContainerInterface $c) {
            $settings = $c->get(Settings::class);
            return new Medoo($settings->get(Medoo::class));
        }
    ]);
};
