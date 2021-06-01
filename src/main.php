<?php declare(strict_types=1);

ini_set('display_errors', "1");
ini_set('display_startup_errors', "1");
error_reporting(E_ALL);

use App\Handlers\HttpErrorHandler;
use App\Handlers\ShutdownHandler;
use App\Middleware\SessionMiddleware;
use App\ResponseEmitter\ResponseEmitter;
use App\Settings\Settings;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\TwigFilter;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/../cache/phpdi');
}

// Set up settings
$settings = require __DIR__ . '/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/dependencies.php';
$dependencies($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Add custom Twig extension
$container->get(Twig::class)->getEnvironment()->addFilter(new TwigFilter('truncate', function ($html, $maxLength) {
    $printedLength = 0;
    $position = 0;
    $tags = array();

    // For UTF-8, we need to count multibyte sequences as one character.
    $re = '{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;|[\x80-\xFF][\x80-\xBF]*}';


    while ($printedLength < $maxLength && preg_match($re, $html, $match, PREG_OFFSET_CAPTURE, $position))
    {
        list($tag, $tagPosition) = $match[0];

        // Print text leading up to the tag.
        $str = substr($html, $position, $tagPosition - $position);
        if ($printedLength + strlen($str) > $maxLength)
        {
            print(substr($str, 0, $maxLength - $printedLength));
            $printedLength = $maxLength;
            break;
        }

        print($str);
        $printedLength += strlen($str);
        if ($printedLength >= $maxLength) break;

        if ($tag[0] == '&' || ord($tag) >= 0x80)
        {
            // Pass the entity or UTF-8 multibyte sequence through unchanged.
            print($tag);
            $printedLength++;
        }
        else
        {
            // Handle the tag.
            $tagName = $match[1][0];
            if ($tag[1] == '/')
            {
                // This is a closing tag.

                $openingTag = array_pop($tags);
                assert($openingTag == $tagName); // check that tags are properly nested.

                print($tag);
            }
            else if ($tag[strlen($tag) - 2] == '/')
            {
                // Self-closing tag.
                print($tag);
            }
            else
            {
                // Opening tag.
                print($tag);
                $tags[] = $tagName;
            }
        }

        // Continue after the tag.
        $position = $tagPosition + strlen($tag);
    }

    // Print any remaining text.
    $ret = "";
    if ($printedLength < $maxLength && $position < strlen($html))
        $ret .= (substr($html, $position, $maxLength - $printedLength));

    // Close any open tags.
    while (!empty($tags))
        $ret .= sprintf('</%s>', array_pop($tags));

    return $ret;
}));

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();
$callableResolver = $app->getCallableResolver();

// Register middleware
$app->add(SessionMiddleware::class);

// Register routes
$routes = require __DIR__ . '/routes.php';
$routes($app);

/** @var Settings $settings */
$settings = $container->get(Settings::class);

$displayErrorDetails = $settings->get('displayErrorDetails');
$logError = $settings->get('logError');
$logErrorDetails = $settings->get('logErrorDetails');

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Error Handler
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory, $container->get(Twig::class));

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Routing Middleware
$app->addRoutingMiddleware();

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

// ITS SUPER IMPORTANT TO ADD THIS MIDDLEWARE AFTER ERROR MIDDLEWARE!!!
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
