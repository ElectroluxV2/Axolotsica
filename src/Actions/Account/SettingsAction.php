<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SettingsAction extends Action {

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function action(): Response {
        $data = $this->request->getParsedBody() ?? $_SESSION["user"];
        $errors = ["show_valid" => true];

        if (!$this->formatCheck($data, $errors)) {
            $this->logger->warning("User failed to provide well formatted data during account settings");
        } else if (!$this->emailAlreadyTakenCheck($data, $errors)) {
            $this->logger->warning("User tried to use already taken email");
        } else if (!$this->updateUser($data)) {
            $this->logger->warning("System failed to update user:", $this->medoo->errorInfo);
        } else {
            $this->logger->warning("User updated his settings");
            $_SESSION["user"]["given_name"] = $data["given_name"];
            $_SESSION["user"]["email"] = $data["email"];
            $_SESSION["user"]["family_name"] = $data["family_name"];
        }

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("account-settings.twig", $data + $errors);
    }

    private function formatCheck(Array $data, Array& $errors): bool {
        // Data format validation
        if (!isset($data["email"]) || empty($data["email"])) $errors["email_error"] = "Please enter email";
        else if (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) $errors["email_error"] = "Email in not valid";

        if (!isset($data["given_name"]) || empty($data["given_name"])) $errors["given_name_error"] = "Please enter given name";
        if (!isset($data["family_name"]) || empty($data["family_name"])) $errors["family_name_error"] = "Please enter name name";

        return count($errors) === 1;
    }

    private function emailAlreadyTakenCheck(Array $data, Array& $errors): bool {
        // Check if there is user with same email already
        $user = $this->medoo->get("users", [
            "email",
            "user_id"
        ], [
            "email" => $data["email"]
        ]);

        if ($user !== null && $user["user_id"] !== $_SESSION["user"]["user_id"]) {
            $errors["email_error"] = "Email is already taken";
        }

        return count($errors) === 1;
    }

    private function updateUser($data): bool {
        $this->medoo->update("users", [
            "given_name" => $data["given_name"],
            "family_name" => $data["family_name"],
            "email" => $data["email"]
        ], [
            "user_id" => $_SESSION["user"]["user_id"]
        ]);

        return $this->medoo->error === null;
    }
}