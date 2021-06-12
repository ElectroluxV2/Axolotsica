<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;

class GroupsJoinAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws Exception
     */
    protected function action(): Response {
        $group_id = $this->args["group_id"];
        $hash = $this->args["hash"];

        $group = $this->medoo->get("groups", [
            "owner_id",
            "group_id",
            "name"
        ], [
            "group_id" => $group_id
        ]);

        // Check Hash
        if (!$this->medoo->has("join_group_hashes", [
            "group_id" => $group_id,
            "hash" => $hash
        ])) {
            throw new Exception("Wrong link");
        }

        // Remove hash
        $this->medoo->delete("join_group_hashes", [
            "group_id" => $group_id,
            "hash" => $hash
        ]);

        // Check if user is already member of this group
        if ($this->medoo->has("members", [
            "user_id" => $_SESSION["user"]["user_id"],
            "group_id" => $group["group_id"]
        ])) {
            throw new Exception("You are already a member of this group!");
        }

        // Add to members
        $this->medoo->insert("members", [
           "user_id" => $_SESSION["user"]["user_id"],
           "group_id" => $group["group_id"]
        ]);

        // Inform owner
        $groupSettingsUrl = RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Groups Settings", [
            "group_id" => $group["group_id"],
            "group_name" => $this->slugs($group["name"])
        ]);

        $this->push($group["owner_id"], $_SESSION["user"]["given_name"]." ".$_SESSION["user"]["family_name"]." joined your group!", $groupSettingsUrl);

        // Forward to Group View
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri(),"Groups View", [
            "group_id" => $group["group_id"],
            "group_name" => $this->slugs($group["name"])
        ]))->withStatus(302);
    }
}