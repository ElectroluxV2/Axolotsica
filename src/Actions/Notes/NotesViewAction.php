<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
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


        return $this->render("notes-view.twig", $note);
    }
}