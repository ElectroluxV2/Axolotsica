<?php declare(strict_types=1);
namespace App\Handlers;

use App\Actions\ActionError;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Views\Twig;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HttpErrorHandler extends SlimErrorHandler {
    protected Twig $twig;

    public function __construct(CallableResolverInterface $callableResolver, ResponseFactoryInterface $responseFactory, Twig $twig, ?LoggerInterface $logger = null) {
        parent::__construct($callableResolver, $responseFactory, $logger);
        $this->twig = $twig;
    }

    /**
     * @inheritdoc
     */
    protected function respond(): Response {
        $this->displayErrorDetails = true;
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ActionError(ActionError::SERVER_ERROR,'An internal error has occurred while processing your request.');

        if ($exception instanceof HttpException) {
            $statusCode = $exception->getCode();
            $error->setDescription($exception->getMessage());

            if ($exception instanceof HttpNotFoundException) {
                $error->setType(ActionError::RESOURCE_NOT_FOUND);
            } elseif ($exception instanceof HttpMethodNotAllowedException) {
                $error->setType(ActionError::NOT_ALLOWED);
            } elseif ($exception instanceof HttpUnauthorizedException) {
                $error->setType(ActionError::UNAUTHENTICATED);
            } elseif ($exception instanceof HttpForbiddenException) {
                $error->setType(ActionError::INSUFFICIENT_PRIVILEGES);
            } elseif ($exception instanceof HttpBadRequestException) {
                $error->setType(ActionError::BAD_REQUEST);
            } elseif ($exception instanceof HttpNotImplementedException) {
                $error->setType(ActionError::NOT_IMPLEMENTED);
            }
        }

        //$a = null;
        if (!($exception instanceof HttpException) && $exception instanceof Throwable && $this->displayErrorDetails) {
            $error->setDescription($exception->getMessage());
            //$a = $exception->getTraceAsString();
        }

        $response = $this->responseFactory->createResponse($statusCode);

        try {
            return $this->twig->render($response, "error.twig", [
                "code" => $statusCode,
                "message" => $error->getDescription()//.' type: '.$error->getType().$a
            ]);
        } catch (LoaderError | RuntimeError | SyntaxError $e) {
            die("An error occurred: {$e->getMessage()}.");
        }
    }
}
