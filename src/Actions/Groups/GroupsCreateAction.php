<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GroupsCreateAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     * @throws SyntaxError
     * @throws LoaderError
     * @throws RuntimeError
     */
    protected function action(): Response {
        $data = $this->request->getParsedBody() ?? [];
        $errors = ["show_valid" => true];

        if (!$this->formatCheck($data, $errors)) {
            $this->logger->warning("User failed to provide well formatted data group creation");
        } else {
            $this->sanitizeData($data);

            // Add to database
            $this->medoo->insert("groups", [
                "owner_id" => $_SESSION["user"]["user_id"],
                "name" => $data["name"]
            ]);

            $group_id = $this->medoo->id();

            // Forward to Group View
            return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->urlFor("Groups View", [
                "group_id" => $group_id,
                "group_name" => $this->slugs($data["name"])
            ]))->withStatus(302);
        }

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("groups-create.twig", $data + $errors);
    }

    private function formatCheck(Array $data, Array& $errors): bool {
        if (!isset($data["name"]) || empty($data["name"])) $errors["name_error"] = "Please enter group name";

        return count($errors) === 1;
    }

    private function sanitizeData(array& $data): void {
        $data["name"] = filter_var($data["name"], FILTER_SANITIZE_STRING);
    }
}