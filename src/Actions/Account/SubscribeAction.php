<?php declare(strict_types=1);
namespace App\Actions\Account;

use App\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

class SubscribeAction extends Action {

    /**
     * @inheritDoc
     * @return Response
     */
    protected function action(): Response {
        $data = $this->request->getParsedBody();
        if (!isset($data["subscription"])) {
            $this->logger->warning("Missing subscription", [
                "data" => $data
            ]);
            return $this->respond('!k');
        }

        $this->medoo->insert("subscriptions", [
            "value_hash" => sha1($data["subscription"]),
            "user_id" => $_SESSION["user"]["user_id"],
            "value" => $data["subscription"]
        ]);

        $this->logger->warning("User enabled notifications");
        return $this->respond('k');
    }
}