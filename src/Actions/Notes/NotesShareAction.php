<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesShareAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {
        $ownedGroups = $this->medoo->select("groups", [
            "group_id",
            "name"
        ], [
            "owner_id" => $_SESSION["user"]["user_id"]
        ]);

        $memberGroups = $this->medoo->select("groups", [
            "[>]members" => ["groups.group_id" => "group_id"]
        ], [
            "groups.group_id",
            "groups.name"
        ], [
            "members.user_id" => $_SESSION["user"]["user_id"]
        ]);

        return $this->render("notes-share.twig", [
            "groups" => $ownedGroups + $memberGroups
        ]);
    }
}