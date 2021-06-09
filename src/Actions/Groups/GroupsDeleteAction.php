<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsDeleteAction extends Action {

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

        $group = $this->medoo->get("groups", [
            "group_id",
            "owner_id",
            "name"
        ], [
            "group_id" => $group_id
        ]);

        $group["sname"] = $this->slugs($group["name"]);

        if ($_SESSION["user"]["user_id"] !== $group["owner_id"]) {
            throw new Exception("Missing permission!");
        }

        if ($this->request->getMethod() === 'GET') {
            return $this->render("groups-delete.twig", [
                "group" => $group
            ]);
        }

        // Delete
        $this->medoo->delete("groups", [
            "group_id" => $group_id
        ]);

        // Forward to groups list
        return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->fullUrlFor($this->request->getUri() ,"Groups List"))->withStatus(302);
    }
}