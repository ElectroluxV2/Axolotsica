<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
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

        if ($this->request->getMethod() === 'GET') {
            return $this->render("notes-delete.twig", [
                "note" => $note
            ]);
        }

        // Delete
        $this->medoo->delete("notes", [
            "note_id" => $note_id
        ]);

        // Forward to notes list
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Notes List"))->withStatus(302);
    }
}