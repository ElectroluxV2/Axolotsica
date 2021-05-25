<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignUpAction extends Action {

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

        if (!$this->formatCheck($data, $errors)) {
            $this->logger->warning("User failed to provide well formatted data during registration");
        } else if (!$this->emailAlreadyTakenCheck($data, $errors)) {
            $this->logger->warning("User tried to use already taken email");
        } else if (!$this->registerUser($data)) {
            $this->logger->warning("System failed to register user:", $this->medoo->errorInfo);
        } else {
            return $this->render("account-created.twig");
        }

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("account-sign-up.twig", $data + $errors);
    }

    private function formatCheck(Array $data, Array& $errors): bool {
        // Data format validation
        if (!isset($data["email"]) || empty($data["email"])) $errors["email_error"] = "Please enter email";
        if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) $errors["email_error"] = "Email in not valid";
        if (!isset($data["email_repeat"]) || empty($data["email_repeat"])) $errors["email_repeat_error"] = "Please repeat email";
        else if ($data["email"] !== $data["email_repeat"]) $errors["email_repeat_error"] = "Emails are not same";

        if (!isset($data["password"]) || empty($data["password"])) $errors["password_error"] = "Please enter password";
        if (!isset($data["password_repeat"]) || empty($data["password_repeat"])) $errors["password_repeat_error"] = "Please repeat password";
        else if ($data["password"] !== $data["password_repeat"]) $errors["password_repeat_error"] = "Passwords are not same";

        if (!isset($data["given_name"]) || empty($data["given_name"])) $errors["given_name_error"] = "Please enter given name";
        if (!isset($data["family_name"]) || empty($data["family_name"])) $errors["family_name_error"] = "Please enter name name";

        return count($errors) === 1;
    }

    private function emailAlreadyTakenCheck(Array $data, Array& $errors): bool {
        // Check if there is user with same email already
        $user = $this->medoo->get("users", [
            "email"
        ], [
            "email" => $data["email"]
        ]);

        if ($user !== null) {
            $errors["email_error"] = "Email is already taken";
        }

        return count($errors) === 1;
    }

    private function registerUser(Array $data): bool {

        $this->medoo->insert("users", [
            "email" => $data["email"],
            "password" => password_hash($data["password"], PASSWORD_DEFAULT),
            "given_name" => $data["given_name"],
            "family_name" => $data["family_name"]
        ]);

        return $this->medoo->error === null;
    }
}