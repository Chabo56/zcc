<?php

declare(strict_types=1);

use Zcc\Core\Theme\ThemeManager;

define('BASE_PATH', __DIR__);

spl_autoload_register(static function (string $class): void {
    $prefix = 'Zcc\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relative) . '.php';

    if (is_file($path)) {
        require $path;
    }
});

$config = require BASE_PATH . '/config/app.php';

if (!empty($config['security']['session_name'])) {
    session_name($config['security']['session_name']);
}

session_start();

$themeManager = new ThemeManager();
$themeManager->bootstrap();

require BASE_PATH . '/app/Core/Security/Headers.php';

return $config;
