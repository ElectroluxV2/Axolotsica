<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsViewAction extends Action {

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
        $group_name = $this->args["group_name"];

        $group = $this->medoo->get("groups", [
            "group_id",
            "owner_id",
            "name"
        ], [
            "group_id" => $group_id
        ]);

        if ($group["owner_id"] !== $_SESSION["user"]["user_id"]) {
            if (!$this->medoo->has("members", [
                "user_id" => $_SESSION["user"]["user_id"],
                "group_id" => $group["group_id"]
            ])) {
                throw new Exception("Missing permission");
            }
        }

        return $this->render("groups-view.twig", [
            "group" => $group,
            "user" => $_SESSION["user"],
            "sname" => $this->slugs($group["name"])
        ]);
    }
}