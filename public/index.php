<?php
declare(strict_types=1);

session_start();

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/config.php';

spl_autoload_register(function (string $class): void {
    $baseDir = BASE_PATH;
    $prefixes = [
        'Core\\'             => $baseDir . '/core/',
        'App\\Controllers\\' => $baseDir . '/app/Controllers/',
        'App\\Models\\'       => $baseDir . '/app/Models/',
    ];

    foreach ($prefixes as $prefix => $dir) {
        if (strpos($class, $prefix) === 0) {
            $relative = substr($class, strlen($prefix));
            $file = $dir . str_replace('\\', '/', $relative) . '.php';
            if (is_readable($file)) {
                require $file;
            }
            return;
        }
    }
});

use Core\DB;
use Core\Router;

DB::configure($config['db'] ?? []);

$router = new Router();
$router->dispatch();