<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesViewAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     * @throws Exception
     */
    protected function action(): Response {
        $note_id = $this->args["note_id"];
        $note_name = $this->args["note_name"];

        $note = $this->medoo->get("notes", [
            "[>]users" => ["notes.owner_id" => "user_id"]
        ], [
            "notes.note_id",
            "notes.name",
            "notes.content",
            "owner" => [
                "users.family_name",
                "users.given_name",
                "users.user_id",
            ]
        ], [
            "notes.note_id" => $note_id
        ]);

        if ($_SESSION["user"]["user_id"] !== $note["owner"]["user_id"]) {
            // Check if its shared note
            $sharedToGroups = $this->medoo->select("notes_sharing", [
                "group_id"
            ], [
                "note_id" => $note_id
            ]);

            $good = false;
            foreach ($sharedToGroups as $group) {
                // If user is member
                if ($this->medoo->has("members", [
                    "group_id" => $group["group_id"],
                    "user_id" => $_SESSION["user"]["user_id"]
                ])) {
                    $good = true;
                    break;
                }
            }
            if (!$good) {
                throw new Exception("Missing permission!");
            }
        }

        return $this->render("notes-view.twig", [
            "note" => $note,
            "user" => $_SESSION["user"],
            "sname" => $this->slugs($note["name"])
        ]);
    }
}