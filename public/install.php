<?php

declare(strict_types=1);

$config = require __DIR__ . '/bootstrap.php';

use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$lockPath = __DIR__ . '/storage/installed.lock';
$dbPath = __DIR__ . '/storage/db.json';
if (is_file($lockPath)) {
    header('Location: /login');
    exit;
}

$settings = new SettingsRepository(__DIR__ . '/storage/settings.json');
$view = new View(__DIR__ . '/resources/views');
$theme = new ThemeManager();

$errors = [];
$dbStatus = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $dbHost = trim((string) ($_POST['db_host'] ?? ''));
        $dbName = trim((string) ($_POST['db_name'] ?? ''));
        $dbUser = trim((string) ($_POST['db_user'] ?? ''));
        $dbPass = (string) ($_POST['db_pass'] ?? '');

        if ($dbHost === '' || $dbName === '' || $dbUser === '') {
            $errors[] = 'DB-Zugangsdaten fehlen.';
        } else {
            try {
                $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $dbHost, $dbName);
                new PDO($dsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
                $dbStatus = 'DB-Verbindung OK.';
                file_put_contents($dbPath, json_encode([
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'database' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPass,
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } catch (Throwable $exception) {
                $errors[] = 'DB-Verbindung fehlgeschlagen: ' . $exception->getMessage();
            }
        }

        $adminUser = trim((string) ($_POST['admin_username'] ?? ''));
        $adminPass = trim((string) ($_POST['admin_password'] ?? ''));

        if ($adminUser === '' || $adminPass === '') {
            $errors[] = 'Admin-Zugangsdaten fehlen.';
        }

        if ($errors === []) {
            $settings->save([
                'admin' => [
                    'username' => $adminUser,
                    'password' => $adminPass,
                ],
                'registration_enabled' => false,
                'theme_default' => 'dark',
                'base_url' => '/',
            ]);

            file_put_contents($lockPath, 'installed');
            header('Location: /login');
            exit;
        }
    }
}

$content = $view->render('system/install.php', [
    'csrf' => Csrf::token(),
    'errors' => $errors,
    'db_status' => $dbStatus,
]);

echo $view->render('layout.php', [
    'title' => 'Installer',
    'content' => $content,
    'menu' => [],
    'user' => null,
    'theme' => $theme->current(),
]);
