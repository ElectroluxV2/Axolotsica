<?php declare(strict_types=1);
namespace App\Actions\Notes;

use App\Actions\Action;
use DOMDocument;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Routing\RouteContext;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class NotesCreateAction extends Action {

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
            $this->logger->warning("User failed to provide well formatted data during note creation");
        } else {
            $this->medoo->insert("notes", [
                "owner_id" => $_SESSION["user"]["user_id"],
                "name" => filter_var($data["name"], FILTER_SANITIZE_STRING),
                "content" => $this->removeScriptTag($data["content"])
            ]);

            if ($this->medoo->error !== null) {
                $this->logger->warning("System failed to create note:", $this->medoo->errorInfo);
                return $this->render("notes-create.twig", $data + $errors);
            }

            // Forward to Note View
            return $this->response->withHeader("Location", RouteContext::fromRequest($this->request)->getRouteParser()->urlFor("Notes View", [
                "note_id" => $this->medoo->id(),
                "note_name" => $this->slugs($data["name"])
            ]))->withStatus(302);
        }

        if ($this->request->getMethod() === "GET") {
            // Do not display errors when form was only requested to show
            $errors = [];
        }

        return $this->render("notes-create.twig", $data + $errors);
    }

    private function formatCheck(Array $data, Array& $errors): bool {
        if (!isset($data["name"]) || empty($data["name"])) $errors["name_error"] = "Please enter note name";
        if (!isset($data["content"]) || empty($data["content"])) $errors["content_error"] = "Please enter note content";
        return count($errors) === 1;
    }

    private function removeScriptTag(string $html): string {
        $doc = new DOMDocument();

        // load the HTML string we want to strip
        $doc->loadHTML($html);

        // get all the script tags
        $script_tags = $doc->getElementsByTagName("script");

        $length = $script_tags->length;

        // for each tag, remove it from the DOM
        for ($i = 0; $i < $length; $i++) {
            $script_tags->item($i)->parentNode->removeChild($script_tags->item($i));
        }

        // get the HTML string back
        return $doc->saveHTML();
    }
}