<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Backup\BackupManager;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Database\DbConnector;
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
$nextcloudSettings = new SettingsRepository(__DIR__ . '/../../storage/nextcloud.json', new DbConnector(__DIR__ . '/../../storage/db.json'));
$manager = new BackupManager(__DIR__ . '/../../storage/backups', $nextcloudSettings);

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'run') {
            try {
                $file = $manager->createDatabaseDump();
                $manager->uploadToNextcloud($file);
                $manager->rotate(7);
                $success = 'Backup erstellt und hochgeladen.';
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

$backups = glob(__DIR__ . '/../../storage/backups/*.sql.gz') ?: [];
rsort($backups);

$content = $view->render('modules/backup.php', [
    'csrf' => Csrf::token(),
    'errors' => $errors,
    'success' => $success,
    'backups' => array_map('basename', $backups),
]);

echo $view->render('layout.php', [
    'title' => 'Backups',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
