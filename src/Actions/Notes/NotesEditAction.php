<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesEditAction extends Action {

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

        if ($this->request->getMethod() === "GET") {
            return $this->render("notes-edit.twig", [
                "note" => $note
            ]);
        }

        // Update
        $data = $this->request->getParsedBody() ?? [];
        $name = empty($data["name"]) ? "Untitled" : filter_var($data["name"], FILTER_SANITIZE_STRING);
        $content = $data["content"];

        $this->medoo->update("notes", [
            "name" => $name,
            "content" => $content
        ], [
            "note_id" => $note_id
        ]);

        // Forward to note view
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri(), "Notes View", [
            "note_id" => $note_id,
            "note_name" => $this->slugs($name)
        ]))->withStatus(302);
    }
}