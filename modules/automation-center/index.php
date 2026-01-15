<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Automation\AutomationStorage;
use Zcc\Core\Database\DbConnector;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$config = require __DIR__ . '/../../bootstrap.php';
$auth = new AuthManager($config['auth'], new SettingsRepository(__DIR__ . '/../../storage/settings.json', new DbConnector(__DIR__ . '/../../storage/db.json')));

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$view = new View(__DIR__ . '/../../resources/views');
$theme = new ThemeManager();
$storage = new AutomationStorage(__DIR__ . '/../../storage/automation', new DbConnector(__DIR__ . '/../../storage/db.json'));

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'settings') {
            $settings = $storage->settings();
            $settings['base_url'] = trim((string) ($_POST['base_url'] ?? ''));
            $settings['shared_secret'] = trim((string) ($_POST['shared_secret'] ?? $settings['shared_secret']));
            $settings['timeout'] = (int) ($_POST['timeout'] ?? 30);
            $storage->saveSettings($settings);
            $success = 'Settings gespeichert.';
        }
    }
}

$content = $view->render('modules/automation.php', [
    'csrf' => Csrf::token(),
    'settings' => $storage->settings(),
    'workflows' => $storage->workflows(),
    'events' => $storage->events(),
    'runs' => $storage->runs(),
    'errors' => $errors,
    'success' => $success,
]);

echo $view->render('layout.php', [
    'title' => 'Automation',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
