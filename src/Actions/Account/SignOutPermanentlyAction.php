<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class SignOutPermanentlyAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {

        // Delete user
        $this->medoo->delete("users", [
            "user_id" => $_SESSION["user"]["user_id"]
        ]);

        session_unset();

        return $this->render("account-deleted.twig");
    }
}