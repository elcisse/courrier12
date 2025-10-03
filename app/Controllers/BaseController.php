<?php
declare(strict_types=1);

namespace App\Controllers;

use Core\Helpers;
use RuntimeException;

abstract class BaseController
{
    protected string $layout = 'layout';
    protected ?array $currentUser = null;

    public function __construct()
    {
        $this->currentUser = $_SESSION['auth_user'] ?? null;

        if ($this->requiresAuthentication() && !$this->currentUser) {
            Helpers::flash('error', 'Veuillez vous connecter pour continuer.');
            Helpers::redirect(Helpers::route('auth', 'login'));
        }
    }

    protected function render(string $view, array $data = []): void
    {
        $errors = Helpers::consumeErrors();
        $dataWithState = array_merge($data, [
            'errors'   => $errors,
            'authUser' => $this->currentUser,
        ]);
        $content = $this->renderView($view, $dataWithState);
        $layoutPath = BASE_PATH . '/app/Views/' . $this->layout . '.php';

        if (is_readable($layoutPath)) {
            $pageTitle = $dataWithState['title'] ?? null;
            $helpers = Helpers::class;
            extract($dataWithState, EXTR_SKIP);
            $authUser = $this->currentUser;
            require $layoutPath;
        } else {
            echo $content;
        }

        Helpers::clearOld();
    }

    protected function renderView(string $view, array $data = []): string
    {
        $viewPath = BASE_PATH . '/app/Views/' . $view . '.php';

        if (!is_readable($viewPath)) {
            throw new RuntimeException('View not found: ' . $view);
        }

        ob_start();
        $helpers = Helpers::class;
        extract($data, EXTR_SKIP);
        $authUser = $this->currentUser;
        require $viewPath;

        return (string) ob_get_clean();
    }

    protected function isPost(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }

    protected function requirePost(): void
    {
        if (!$this->isPost()) {
            http_response_code(405);
            echo 'Method Not Allowed';
            exit;
        }
    }

    protected function validateCsrfOrFail(): void
    {
        $token = $_POST['_token'] ?? $_GET['_token'] ?? null;

        if (!Helpers::validateCsrf($token)) {
            http_response_code(419);
            echo 'Invalid or missing CSRF token.';
            exit;
        }
    }

    protected function input(string $key, $default = null)
    {
        $value = $_POST[$key] ?? $_GET[$key] ?? $default;

        if (is_string($value)) {
            return trim($value);
        }

        return $value;
    }

    protected function redirect(string $controller, string $action = 'index', array $params = []): void
    {
        Helpers::redirect(Helpers::route($controller, $action, $params));
    }

    protected function requiresAuthentication(): bool
    {
        return true;
    }

    protected function user(): ?array
    {
        return $this->currentUser;
    }

    protected function userId(): ?int
    {
        return $this->currentUser['id'] ?? null;
    }
}





