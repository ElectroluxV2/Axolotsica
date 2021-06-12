<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsRemoveUserAction extends Action {

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
        $user_id = $this->args["user_id"];

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

        $user = $this->medoo->get("users", [
            "user_id",
            "family_name",
            "given_name"
        ], [
            "user_id" => $user_id
        ]);

        if ($this->request->getMethod() === 'GET') {
            return $this->render("groups-remove-user.twig", [
                "group" => $group,
                "removeUser" => $user
            ]);
        }

        // Delete
        $this->medoo->delete("members", [
            "group_id" => $group_id,
            "user_id" => $user_id
        ]);

        // Forward to group settings
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Groups Settings", [
            "group_id" => $group_id,
            "group_name" => $group["sname"]
        ]))->withStatus(302);
    }
}