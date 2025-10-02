<?php
declare(strict_types=1);

namespace Core;

class Helpers
{
    public static function sanitize(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    public static function route(string $controller, string $action = 'index', array $params = []): string
    {
        $query = array_merge([
            'controller' => $controller,
            'action'     => $action,
        ], $params);

        return 'index.php?' . http_build_query($query);
    }

    public static function flash(string $key, ?string $message = null)
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }

        if (!isset($_SESSION['flash'][$key])) {
            return null;
        }

        $value = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);

        return $value;
    }

    public static function storeOld(array $data): void
    {
        $_SESSION['old'] = $data;
    }

    public static function old(string $key, $default = '')
    {
        if (!isset($_SESSION['old'][$key])) {
            return $default;
        }

        return $_SESSION['old'][$key];
    }

    public static function clearOld(): void
    {
        unset($_SESSION['old']);
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(?string $token): bool
    {
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function errors(?array $errors = null): array
    {
        if ($errors !== null) {
            $_SESSION['errors'] = $errors;
            return $errors;
        }

        return $_SESSION['errors'] ?? [];
    }

    public static function consumeErrors(): array
    {
        $errors = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);

        return $errors;
    }
}