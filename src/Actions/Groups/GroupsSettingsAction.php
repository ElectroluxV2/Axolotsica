<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsSettingsAction extends Action {

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

        if (!$group) {
            throw new Exception("Group not found");
        }

        $members = $this->medoo->select("members", [
            "[>]users" => ["members.user_id" => "user_id"]
        ], [
            "users.user_id",
            "family_name",
            "given_name"
        ], [
            "group_id" => $group_id
        ]);

        return $this->render("groups-settings.twig", [
            "members" => $members,
            "group" => $group,
            "user" => $_SESSION["user"]
        ]);
    }
}