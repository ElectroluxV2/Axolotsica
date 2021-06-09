<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsShareAction extends Action {

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
        $hash = substr(str_shuffle(MD5(microtime())), 0, 10);

        $group = $this->medoo->get("groups", [
            "group_id",
            "name",
            "owner_id"
        ], [
            "group_id" => $group_id
        ]);

        if ($_SESSION["user"]["user_id"] !== $group["owner_id"]) {
            throw new Exception("Missing permission!");
        }

        // Insert hash to database
        $this->medoo->insert("join_group_hashes", [
            "group_id" => $group_id,
            "hash" => $hash
        ]);

        return $this->render("groups-share.twig", [
           "group" => $group,
           "hash" => $hash
        ]);
    }
}