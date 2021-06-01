<?php declare(strict_types=1);
namespace App\Actions;

use ErrorException;
use Medoo\Medoo;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

abstract class Action {
    protected LoggerInterface $logger;
    protected Twig $twig;
    protected Medoo $medoo;
    protected WebPush $webPush;
    protected Request $request;
    protected Response $response;
    protected array $args;

    public function __construct(LoggerInterface $logger, Twig $twig, Medoo $medoo, WebPush $webPush) {
        $this->logger = $logger;
        $this->twig = $twig;
        $this->medoo = $medoo;
        $this->webPush = $webPush;
    }

    /**
     * @throws HttpBadRequestException
     */
    public function __invoke(Request $request, Response $response, array $args): Response {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        return $this->action();
    }

    /**
     * @throws HttpBadRequestException
     */
    abstract protected function action(): Response;

    /**
     * @throws HttpBadRequestException
     */
    protected function resolveArg(string $name): mixed {
        if (!isset($this->args[$name])) {
            throw new HttpBadRequestException($this->request, "Could not resolve argument `{$name}`.");
        }

        return $this->args[$name];
    }

    protected function respond(string $body): Response {
        $this->response->getBody()->write($body);
        return $this->response;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    protected function render(string $template, $data = []): Response {
        return $this->twig->render($this->response, $template, $data);
    }

    protected function slugs(string $text): string {// replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate divider
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    /**
     * @throws ErrorException
     */
    protected function push(int | string $user_id, $payload): bool {
        $subscriptions = $this->medoo->select("subscriptions", [
            "[>]users" => ["users.user_id" => "user_id"]
        ], [
            "subscriptions.value",
            "users.family_name",
            "users.given_name",
        ], [
            "subscriptions.user_id" => $user_id
        ]);

        $report = null;
        foreach ($subscriptions as $subscription) {

            $report = $this->webPush->sendOneNotification(
                Subscription::create(json_decode($subscription["value"], true)),
                $payload
            );

            if (!$report?->isSuccess()) {
                $this->logger->error("Failed to send push {$report->getReason()}");
            }
        }

        return $report?->isSuccess() ?? true;
    }
}
