<?php declare(strict_types=1);
namespace App\Actions;

use ErrorException;
use Psr\Http\Message\ResponseInterface as Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class HomeAction extends Action {

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    protected function action(): Response {

        $ownNotes = $this->medoo->select("notes", [
            "note_id",
            "name",
            "content"
        ], [
            "owner_id" => $_SESSION["user"]["user_id"]
        ]);

        $joinedGroups = $this->medoo->select("members", [
            "group_id"
        ], [
            "user_id" => $_SESSION["user"]["user_id"]
        ]);

        $sharedNotes = [];
        foreach ($joinedGroups as $joinedGroup) {
            array_push($sharedNotes,$this->medoo->select("notes", [
                "[>]notes_sharing" => ["notes.note_id" => "note_id"]
            ], [
                "notes.note_id",
                "notes.name",
                "notes.content"
            ], [
                "group_id" => $joinedGroup["group_id"]
            ]));
        }

        return $this->render("home.twig", [
            "user" => $_SESSION["user"],
            "own_notes" => $ownNotes,
            "shared_notes" => $sharedNotes
        ]);
    }
}