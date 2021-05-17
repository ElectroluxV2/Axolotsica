<?php declare(strict_types=1);
namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class TestAction extends Action {


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function action(): Response {
        return $this->render("test.html", [
           "name" => $this->args["name"] ?? "n\a"
        ]);
    }
}