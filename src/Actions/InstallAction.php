<?php declare(strict_types=1);
namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class InstallAction extends Action {

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function action(): Response {

        $this->medoo->create("users", [
            "user_id" => ["int", "auto_increment", "not null"],
            "given_name" => ["varchar(128)", "not null"],
            "family_name" => ["varchar(128)", "not null"],
            "email" => ["varchar(512)", "not null"],
            "password" => ["varchar(512)", "not null"],
            "verified" => ["boolean", "default false"],
            "primary key (<user_id>)"
        ]);


        return $this->render("install.twig", [

        ]);
    }
}