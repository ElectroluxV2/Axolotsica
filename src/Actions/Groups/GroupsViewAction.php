<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsViewAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Exception
     */
    protected function action(): Response {
        $group_id = $this->args["group_id"];

        $group = $this->medoo->get("groups", [
            "group_id",
            "owner_id",
            "name"
        ], [
            "group_id" => $group_id
        ]);

        $group["sname"] = $this->slugs($group["name"]);

        if ($group["owner_id"] !== $_SESSION["user"]["user_id"]) {
            if (!$this->medoo->has("members", [
                "user_id" => $_SESSION["user"]["user_id"],
                "group_id" => $group["group_id"]
            ])) {
                throw new Exception("Missing permission");
            }
        }

        $notes = $this->medoo->select("notes", [
            "[>]notes_sharing" => ["notes.note_id" => "note_id"]
        ], [
            "notes.note_id",
            "notes.name",
            "notes.content"
        ], [
            "group_id" => $group["group_id"]
        ]);

        return $this->render("groups-view.twig", [
            "group" => $group,
            "notes" => $notes,
            "user" => $_SESSION["user"]
        ]);
    }
}