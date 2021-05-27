<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
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

        if (!$this->formatCheck($data, $errors)) {
            if (!empty($data)) $this->logger->warning("User failed to provide well formatted data during sign in");
        } else if (!$this->loginUser($data, $errors)) {
            $this->logger->warning("User failed to login");
        } else {
            // Back to requested page
            if (isset($_SESSION["forward_to"])) {
                $this->logger->info("Found forward to ${_SESSION["forward_to"]}, forwarding");
                return $this->response->withHeader("Location", $_SESSION["forward_to"])->withStatus(302);
            }

            // Forward to home
            return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->urlFor("Home"))->withStatus(302);
        }

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("account-sign-in.twig", $data + $errors);
    }

    private function formatCheck(Array $data, Array& $errors): bool {
        if (!isset($data["email"]) || empty($data["email"])) $errors["email_error"] = "Please enter email";
        if (isset($data["email"]) && !filter_var($data["email"], FILTER_VALIDATE_EMAIL)) $errors["email_error"] = "Email is not valid";
        if (!isset($data["password"]) || empty($data["password"])) $errors["password_error"] = "Please enter password";

        return count($errors) === 1;
    }

    private function loginUser(Array $data, Array& $errors): bool {

        $user = $this->medoo->get("users", [
            "user_id",
            "email",
            "given_name",
            "family_name",
            "verified",
            "password"
        ], [
            "email" => $data["email"]
        ]);

        if ($this->medoo->errorInfo !== null) {
            $this->logger->warning("System failed to login user:", $this->medoo->errorInfo);
            return false;
        }

        if ($user === null) {
            $this->logger->warning("User tried to login with email that doesn't exists");
            $errors["email_error"] = "There is no user with this email address";
            return false;
        }

        if (!password_verify($data["password"], $user["password"])) {
            $this->logger->warning("User tried to login with wrong password");
            $errors["password_error"] = "Wrong password";
            return false;
        }

        $_SESSION["user"] = $user;
        return true;
    }
}