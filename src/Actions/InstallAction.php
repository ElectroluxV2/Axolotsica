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

        $this->medoo->create("notes", [
            "note_id" => ["int", "auto_increment", "not null"],
            "name" => ["varchar(512)", "not null"],
            "content" => ["text", "not null"],
            "owner_id" => ["int", "not null"],
            "primary key (<note_id>)",
            "constraint notes_users_user_id_fk foreign key (<owner_id>) references users(<user_id>) on delete cascade"
        ]);

        $this->medoo->create("groups", [
            "group_id" => ["int", "auto_increment", "not null"],
            "name" => ["varchar(512)", "not null"],
            "owner_id" => ["int", "not null"],
            "primary key (<group_id>)",
            "constraint groups_users_user_id_fk foreign key (<owner_id>) references users(<user_id>) on delete cascade"
        ]);

        $this->medoo->create("members", [
            "user_id" => ["int", "not null"],
            "group_id" => ["int", "not null"],
            "primary key (<user_id>, <group_id>)",
            "constraint members_users_user_id_fk foreign key (<user_id>) references users(<user_id>) on delete cascade",
            "constraint members_groups_group_id_fk foreign key (<group_id>) references groups(<group_id>) on delete cascade",
        ]);

        $this->medoo->create("subscriptions", [
            "subscription_id" => ["int", "auto_increment", "not null"],
            "user_id" => ["int", "not null"],
            "value" => ["text", "not null"],
            "primary key (<subscription_id>)",
            "constraint subscriptions_users_user_id_fk foreign key (<user_id>) references users(<user_id>) on delete cascade",
        ]);

        $this->medoo->create("notes_sharing", [
            "group_id" => ["int", "not null"],
            "note_id" => ["int", "not null"],
            "primary key (<group_id>, <note_id>)",
            "constraint notes_sharing_groups_group_id_fk foreign key (<group_id>) references groups(<group_id>) on delete cascade",
            "constraint notes_sharing_notes_note_id foreign key (<note_id>) references notes(<note_id>) on delete cascade",
        ]);

        return $this->render("install.twig", [

        ]);
    }
}