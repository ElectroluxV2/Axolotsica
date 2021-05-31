<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesListAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {
        $notes = $this->medoo->select("notes", [
            "note_id",
            "name",
            "content",
        ], [
            "owner_id" => $_SESSION["user"]["user_id"]
        ]);

        return $this->render("notes-list.twig", [
            "notes" => $notes
        ]);
    }
}