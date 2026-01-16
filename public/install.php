<?php

declare(strict_types=1);

$config = require __DIR__ . '/bootstrap.php';

use Zcc\Core\Security\Csrf;
use Zcc\Core\Database\Database;
use Zcc\Core\Database\DbConnector;
use Zcc\Core\Modules\ModuleRegistry;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Users\UserRepository;
use Zcc\Core\Views\View;

$lockPath = __DIR__ . '/storage/installed.lock';
$dbPath = __DIR__ . '/storage/db.json';
if (is_file($lockPath)) {
    header('Location: /login');
    exit;
}

$settings = new SettingsRepository(__DIR__ . '/storage/settings.json', null, 'core');
$view = new View(__DIR__ . '/resources/views');
$theme = new ThemeManager();

$errors = [];
$dbStatus = null;
$schemaStatus = null;
$step = (int) ($_GET['step'] ?? $_POST['step'] ?? 1);
$step = max(1, min(3, $step));

if (!isset($_SESSION['install'])) {
    $_SESSION['install'] = [];
}

$sessionInstall = &$_SESSION['install'];
if (is_file($dbPath)) {
    $sessionInstall['db_ready'] = true;
}

if ($step > 1 && empty($sessionInstall['db_ready'])) {
    header('Location: /install.php?step=1');
    exit;
}

if ($step > 2 && empty($sessionInstall['schema_ready'])) {
    header('Location: /install.php?step=2');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        if ($step === 1) {
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
                    $sessionInstall['db_ready'] = true;
                } catch (Throwable $exception) {
                    $errors[] = 'DB-Verbindung fehlgeschlagen: ' . $exception->getMessage();
                }
            }

            if ($errors === []) {
                header('Location: /install.php?step=2');
                exit;
            }
        }

        if ($step === 2) {
            try {
                if (($_POST['apply_schema'] ?? '') === '1') {
                    $database = new Database($dbPath);
                    $database->applySchema(__DIR__ . '/storage/schema.sql');
                    $schemaStatus = 'Schema angewendet.';
                }

                if (($_POST['seed_modules'] ?? '') === '1') {
                    $connector = new DbConnector($dbPath);
                    $registry = new ModuleRegistry(__DIR__ . '/storage/modules.json', $connector);
                    $registry->save($registry->all());
                    $schemaStatus = $schemaStatus ? $schemaStatus . ' Module gesät.' : 'Module gesät.';
                }
                $sessionInstall['schema_ready'] = true;
            } catch (Throwable $exception) {
                $errors[] = 'Schema/Seed fehlgeschlagen: ' . $exception->getMessage();
            }

            if ($errors === []) {
                header('Location: /install.php?step=3');
                exit;
            }
        }

        if ($step === 3) {
            $adminUser = trim((string) ($_POST['admin_username'] ?? ''));
            $adminPass = trim((string) ($_POST['admin_password'] ?? ''));

            if ($adminUser === '' || $adminPass === '') {
                $errors[] = 'Admin-Zugangsdaten fehlen.';
            }

            if ($errors === []) {
                $settings = new SettingsRepository(__DIR__ . '/storage/settings.json', new DbConnector($dbPath), 'core');
                $settings->save([
                    'admin' => [
                        'username' => $adminUser,
                        'password' => $adminPass,
                    ],
                    'registration_enabled' => false,
                    'theme_default' => 'dark',
                    'base_url' => '/',
                ]);

                $users = new UserRepository(new DbConnector($dbPath));
                if ($users->isAvailable()) {
                    $users->upsert($adminUser, $adminPass, 'admin', []);
                }

                file_put_contents($lockPath, 'installed');
                unset($_SESSION['install']);
                header('Location: /login');
                exit;
            }
        }
    }
}

$content = $view->render('system/install.php', [
    'csrf' => Csrf::token(),
    'errors' => $errors,
    'db_status' => $dbStatus,
    'schema_status' => $schemaStatus,
    'step' => $step,
]);

echo $view->render('layout.php', [
    'title' => 'Installer',
    'content' => $content,
    'menu' => [],
    'user' => null,
    'theme' => $theme->current(),
]);
