<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
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
     * @throws Exception
     */
    protected function action(): Response {
        $note_id = $this->args["note_id"];
        $note_name = $this->args["note_name"];

        // Select groups to share
        if ($this->request->getMethod() === "GET") {
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

        $selectedGroupsIds = $this->request->getParsedBody()["groups"];

        if (count($selectedGroupsIds) === 0) {
            throw new Exception("You have to select at least one group");
        }

        foreach ($selectedGroupsIds as $group_id) {
            $this->medoo->insert("notes_sharing", [
                "note_id" => $note_id,
                "group_id" => $group_id
            ]);
        }

        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Notes View", [
            "note_id" => $note_id,
            "note_name" => $note_name
        ]))->withStatus(302);

    }
}