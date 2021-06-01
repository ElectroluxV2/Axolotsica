<?php declare(strict_types=1);
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Routing\RouteContext;

class RequireAccountMiddleware implements Middleware {
    private ResponseFactory $responseFactory;
    private LoggerInterface $logger;

    public function __construct(ResponseFactory $responseFactory, LoggerInterface $logger) {
        $this->responseFactory = $responseFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response {
        // Logged in
        if (isset($_SESSION["user"])) {
            return $handler->handle($request);
        }

        // Redirect to login page and save destination
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        $url = $routeParser->fullUrlFor($request->getUri(), $routeContext->getRoute()->getName(), $routeContext->getRoute()->getArguments());
        $_SESSION["forward_to"] = $url;

        $this->logger->info("Unauthenticated user tried to visit ${url}, forwarding to login page and saving destination");

        $response = $this->responseFactory->createResponse(302);
        return $response->withHeader("Location", $routeParser->fullUrlFor($request->getUri(), "Account Sign In"));
    }
}
