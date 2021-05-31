<?php declare(strict_types=1);
namespace App\Actions\Groups;

use App\Actions\Action;
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
     */
    protected function action(): Response {


        // TODO: Implement action() method.
        return $this->render("groups-view.twig", [

        ]);
    }
}