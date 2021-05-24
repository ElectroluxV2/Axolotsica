<?php declare(strict_types=1);
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class SessionMiddleware implements Middleware {
    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response {
        if (session_status() == PHP_SESSION_NONE){
            session_start();
            $_SESSION["logged_in"] = false;
        }

        return $handler->handle($request);
    }
}
