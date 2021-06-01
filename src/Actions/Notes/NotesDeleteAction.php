<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesDeleteAction extends Action {

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

        $note = $this->medoo->get("notes", [
            "note_id",
            "owner_id",
            "name",
            "content"
        ], [
            "note_id" => $note_id
        ]);

        if ($_SESSION["user"]["user_id"] !== $note["owner_id"]) {
            throw new Exception("Missing permission!");
        }

        return $this->render("notes-delete.twig", []);
    }
}