<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class SignOutAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     */
    protected function action(): Response {
        session_unset();

        // Forward to home
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->urlFor("Home"))->withStatus(302);

    }
}