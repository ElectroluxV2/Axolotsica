<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsListAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {

        $groups = $this->medoo->select("groups", [
            "[>]users" => ["groups.owner_id" => "user_id"]
        ],[
           "groups.group_id",
           "groups.name",
           "groups.owner_id",
           "users.given_name",
           "users.family_name",
        ]);

        // TODO: Implement action() method.
        return $this->render("groups-list.twig", [
            "groups" => $groups,
        ]);
    }
}