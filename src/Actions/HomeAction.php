<?php declare(strict_types=1);
namespace App\Actions;

use ErrorException;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeAction extends Action {

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws ErrorException
     */
    protected function action(): Response {

        return $this->render("home.twig", $_SESSION["user"]);
    }
}