<?php

declare(strict_types=1);

$config = require __DIR__ . '/bootstrap.php';

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Modules\ModuleRegistry;
use Zcc\Core\Audit\AuditLogger;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$settings = new SettingsRepository(__DIR__ . '/storage/settings.json');
$auth = new AuthManager($config['auth'], $settings);
if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$registry = new ModuleRegistry(__DIR__ . '/storage/modules.json');
$audit = new AuditLogger(__DIR__ . '/storage/audit.log');
$view = new View(__DIR__ . '/resources/views');
$theme = new ThemeManager();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'toggle') {
        $key = (string) ($_POST['key'] ?? '');
        $enabled = ($_POST['enabled'] ?? '') === '1';
        $registry->setEnabled($key, $enabled);
        $audit->log('module.toggled', ['key' => $key, 'enabled' => $enabled, 'user' => $auth->user()['username'] ?? '']);
        $success = $enabled ? 'Modul aktiviert.' : 'Modul deaktiviert.';
    }

    if ($action === 'upload') {
        $enableAfter = ($_POST['enable_after'] ?? '') === '1';
        $file = $_FILES['module_zip'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload fehlgeschlagen.';
        } else {
            try {
                $module = installModuleFromZip($file['tmp_name'], __DIR__ . '/modules');
                $module['enabled'] = $enableAfter;
                $registry->register($module);
                $audit->log('module.installed', ['key' => $module['key'] ?? '', 'user' => $auth->user()['username'] ?? '']);
                $success = 'Modul installiert: ' . ($module['name'] ?? $module['key']);
            } catch (Throwable $exception) {
                $errors[] = $exception->getMessage();
            }
        }
    }
}

$modules = $registry->all();

$content = $view->render('modules/manage.php', [
    'modules' => $modules,
    'csrf' => Csrf::token(),
    'errors' => $errors,
    'success' => $success,
]);

echo $view->render('layout.php', [
    'title' => 'Modules',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);

function installModuleFromZip(string $zipPath, string $modulesBasePath): array
{
    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new RuntimeException('ZIP konnte nicht geöffnet werden.');
    }

    $moduleJson = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === 'module.json') {
            $moduleJson = $zip->getFromIndex($i);
            break;
        }
    }

    if ($moduleJson === null) {
        $zip->close();
        throw new RuntimeException('module.json fehlt im ZIP-Root.');
    }

    $module = json_decode($moduleJson, true);
    if (!is_array($module)) {
        $zip->close();
        throw new RuntimeException('module.json ist ungültig.');
    }

    $key = $module['key'] ?? '';
    if (!is_string($key) || !preg_match('/^[a-z0-9\-]+$/i', $key)) {
        $zip->close();
        throw new RuntimeException('Ungültiger Modul-Key.');
    }

    $targetPath = rtrim($modulesBasePath, '/') . '/' . $key;
    if (is_dir($targetPath)) {
        removeDirectory($targetPath);
    }

    if (!mkdir($targetPath, 0755, true) && !is_dir($targetPath)) {
        $zip->close();
        throw new RuntimeException('Konnte Modul-Verzeichnis nicht erstellen.');
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === 'module.json') {
            continue;
        }

        $safeName = ltrim($name, '/');
        if ($safeName === '' || str_contains($safeName, '..') || str_contains($safeName, '\\')) {
            continue;
        }

        $destination = $targetPath . '/' . $safeName;
        if (str_ends_with($name, '/')) {
            if (!is_dir($destination) && !mkdir($destination, 0755, true)) {
                $zip->close();
                throw new RuntimeException('Konnte Ordner nicht erstellen: ' . $safeName);
            }
            continue;
        }

        $dir = dirname($destination);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $zip->close();
            throw new RuntimeException('Konnte Pfad nicht erstellen: ' . $dir);
        }

        $stream = $zip->getStream($name);
        if ($stream === false) {
            $zip->close();
            throw new RuntimeException('Konnte Datei nicht lesen: ' . $safeName);
        }

        $contents = stream_get_contents($stream);
        fclose($stream);

        if ($contents === false || file_put_contents($destination, $contents) === false) {
            $zip->close();
            throw new RuntimeException('Konnte Datei nicht schreiben: ' . $safeName);
        }
    }

    $zip->close();

    $modulePath = $targetPath . '/module.json';
    if (file_put_contents($modulePath, $moduleJson) === false) {
        throw new RuntimeException('module.json konnte nicht gespeichert werden.');
    }

    return [
        'key' => $key,
        'name' => (string) ($module['name'] ?? $key),
        'version' => (string) ($module['version'] ?? '0.0.0'),
        'description' => (string) ($module['description'] ?? ''),
        'permissions' => $module['permissions'] ?? [],
        'menu' => $module['menu'] ?? [],
        'submenu' => $module['submenu'] ?? [],
    ];
}

function removeDirectory(string $path): void
{
    if (!is_dir($path)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($path);
}
