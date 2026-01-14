<?php

declare(strict_types=1);

$config = require __DIR__ . '/bootstrap.php';

use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$lockPath = __DIR__ . '/storage/installed.lock';
if (is_file($lockPath)) {
    header('Location: /login');
    exit;
}

$settings = new SettingsRepository(__DIR__ . '/storage/settings.json');
$view = new View(__DIR__ . '/resources/views');
$theme = new ThemeManager();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $adminUser = trim((string) ($_POST['admin_username'] ?? ''));
        $adminPass = trim((string) ($_POST['admin_password'] ?? ''));

        if ($adminUser === '' || $adminPass === '') {
            $errors[] = 'Admin-Zugangsdaten fehlen.';
        } else {
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
]);

echo $view->render('layout.php', [
    'title' => 'Installer',
    'content' => $content,
    'menu' => [],
    'user' => null,
    'theme' => $theme->current(),
]);
