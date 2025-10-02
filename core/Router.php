<?php
declare(strict_types=1);

namespace Core;

use Throwable;

class Router
{
    public function dispatch(): void
    {
        $controllerParam = isset($_GET['controller']) ? trim((string) $_GET['controller']) : 'home';
        $actionParam = isset($_GET['action']) ? trim((string) $_GET['action']) : 'index';

        $controllerName = $this->formatControllerName($controllerParam);
        $controllerClass = 'App\\Controllers\\' . $controllerName;

        if (!class_exists($controllerClass)) {
            $this->abort(404, 'Controller not found.');
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $actionParam)) {
            $this->abort(404, 'Action not found.');
            return;
        }

        try {
            $controller->{$actionParam}();
        } catch (Throwable $throwable) {
            $this->abort(500, 'An unexpected error occurred.', $throwable);
        }
    }

    private function formatControllerName(string $controller): string
    {
        $controller = strtolower($controller);
        $controller = str_replace(['-', '_'], ' ', $controller);
        $controller = str_replace(' ', '', ucwords($controller));

        return $controller . 'Controller';
    }

    private function abort(int $status, string $message, ?Throwable $throwable = null): void
    {
        http_response_code($status);
        echo '<h1>' . htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') . '</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>';

        if ($throwable && ini_get('display_errors')) {
            echo '<pre>' . htmlspecialchars($throwable->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
        }
    }
}