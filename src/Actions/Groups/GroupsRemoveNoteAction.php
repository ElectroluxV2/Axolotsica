<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsRemoveNoteAction extends Action {

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
        $note_id = $this->args["note_id"];

        $group = $this->medoo->get("groups", [
            "group_id",
            "owner_id",
            "name"
        ], [
            "group_id" => $group_id
        ]);

        if (!$group) {
            throw new Exception("Group not found");
        }

        $group["sname"] = $this->slugs($group["name"]);

        if ($_SESSION["user"]["user_id"] !== $group["owner_id"]) {
            throw new Exception("Missing permission!");
        }

        $note = $this->medoo->get("notes", [
            "note_id",
            "name",
        ], [
            "note_id" => $note_id
        ]);

        if (!$note) {
            throw new Exception("Note #$note_id not found!");
        }

        if ($this->request->getMethod() === 'GET') {
            return $this->render("groups-remove-note.twig", [
                "group" => $group,
                "note" => $note
            ]);
        }

        // Delete
        $this->medoo->delete("notes_sharing", [
            "group_id" => $group_id,
            "note_id" => $note_id
        ]);

        // Forward to group view
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Groups View", [
            "group_id" => $group_id,
            "group_name" => $group["sname"]
        ]))->withStatus(302);
    }
}