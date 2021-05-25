<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignInAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {
        $data = $this->request->getParsedBody() ?? [];
        $errors = ["show_valid" => true];

        if (!isset($data["email"]) || empty($data["email"])) $errors["email_error"] = "Please enter email";
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) $errors["email_error"] = "Email in not valid";
        if (!isset($data["password"]) || empty($data["password"])) $errors["password_error"] = "Please enter password";

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("account-sign-in.twig", $data + $errors);
    }
}