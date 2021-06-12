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

        $noteUrl = RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Notes View", [
            "note_id" => $note_id,
            "note_name" => $note_name
        ]);

        foreach ($selectedGroupsIds as $group_id) {

            // Check if user is already member of this group
            if ($this->medoo->has("notes_sharing", [
                "note_id" => $note_id,
                "group_id" => $group_id
            ])) {
                throw new Exception("You are already shared this note to group #$group_id!");
            }

            $this->medoo->insert("notes_sharing", [
                "note_id" => $note_id,
                "group_id" => $group_id
            ]);

            // Send push message to all members
            $members = $this->medoo->select("members", [
                "[>]users" => ["members.user_id" => "user_id"],
            ], [
                "users.user_id",
                "given_name",
                "family_name",
            ], [
                "group_id" => $group_id
            ]);

            foreach ($members as $user) {
                $this->logger->info("member", $user);
                $this->push($user["user_id"],
                    "Hey ".$user["family_name"]."! ".$_SESSION["user"]["given_name"]." ".$_SESSION["user"]["family_name"]." created new note!",
                    $noteUrl
                );
            }
        }

        return $this->response->withHeader("Location", $noteUrl)->withStatus(302);
    }
}