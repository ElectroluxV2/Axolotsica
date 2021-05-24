<?php declare(strict_types=1);
namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeAction extends Action {

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    protected function action(): Response {
        // TODO: Implement action() method.

        return $this->render("login.html");
    }
}