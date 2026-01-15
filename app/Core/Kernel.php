<?php

declare(strict_types=1);

namespace Zcc\Core;

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Automation\AutomationStorage;
use Zcc\Core\Audit\AuditLogger;
use Zcc\Core\Http\Request;
use Zcc\Core\Http\Response;
use Zcc\Core\Http\Router;
use Zcc\Core\Permissions\PermissionGate;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

final class Kernel
{
    public function __construct(private readonly array $config)
    {
    }

    public function handle(Request $request): Response
    {
        if (!is_file(BASE_PATH . '/storage/installed.lock') && $request->path !== '/install.php') {
            return Response::redirect('/install.php');
        }

        $router = new Router();
        $view = new View(BASE_PATH . '/resources/views');
        $settings = new SettingsRepository(BASE_PATH . '/storage/settings.json');
        $auth = new AuthManager($this->config['auth'], $settings);
        $theme = new ThemeManager();
        $audit = new AuditLogger(BASE_PATH . '/storage/audit.log');
        $gate = new PermissionGate();

        $router->get('/', static function () use ($view, $auth, $theme): Response {
            $content = $view->render('dashboard.php', [
                'user' => $auth->user(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Dashboard',
                'content' => $content,
                'menu' => require BASE_PATH . '/config/menu.php',
                'user' => $auth->user(),
                'theme' => $theme->current(),
            ]));
        });

        $router->get('/login', static function () use ($view, $theme): Response {
            $content = $view->render('login.php', [
                'csrf' => Csrf::token(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Login',
                'content' => $content,
                'menu' => [],
                'user' => null,
                'theme' => $theme->current(),
            ]));
        });

        $router->post('/login', static function (Request $request) use ($auth, $audit): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $username = (string) $request->input('username');
            $password = (string) $request->input('password');

            if (!$auth->attempt($username, $password)) {
                return Response::redirect('/login?error=1');
            }

            $audit->log('auth.login', ['user' => $username]);

            return Response::redirect('/');
        });

        $router->post('/logout', static function (Request $request) use ($auth, $audit): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            if ($auth->user()) {
                $audit->log('auth.logout', ['user' => $auth->user()['username'] ?? '']);
            }

            $auth->logout();

            return Response::redirect('/login');
        });

        $router->post('/theme', static function (Request $request) use ($theme): Response {
            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $choice = $request->input('theme');
            if (!in_array($choice, ['light', 'dark'], true)) {
                return new Response('Invalid theme', 400);
            }

            setcookie(ThemeManager::COOKIE_NAME, $choice, time() + 31536000, '/');

            return Response::redirect('/');
        });

        $router->get('/system/settings', static function () use ($view, $auth, $theme, $settings, $gate): Response {
            if (!$gate->allows($auth->user(), 'system.settings')) {
                return new Response('Forbidden', 403);
            }

            $content = $view->render('system/settings.php', [
                'csrf' => Csrf::token(),
                'settings' => $settings->all(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Settings',
                'content' => $content,
                'menu' => require BASE_PATH . '/config/menu.php',
                'user' => $auth->user(),
                'theme' => $theme->current(),
            ]));
        });

        $router->post('/system/settings', static function (Request $request) use ($settings, $auth, $gate, $audit): Response {
            if (!$gate->allows($auth->user(), 'system.settings')) {
                return new Response('Forbidden', 403);
            }

            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            $settings->set('base_url', (string) $request->input('base_url', '/'));
            $settings->set('registration_enabled', (bool) $request->input('registration_enabled', false));
            $settings->set('theme_default', (string) $request->input('theme_default', 'dark'));
            $settings->set('admin', [
                'username' => (string) $request->input('admin_username', 'admin'),
                'password' => (string) $request->input('admin_password', 'admin'),
            ]);

            $audit->log('settings.updated', ['user' => $auth->user()['username'] ?? '']);

            return Response::redirect('/system/settings');
        });

        $router->get('/system/audit', static function () use ($view, $auth, $theme, $audit, $gate): Response {
            if (!$gate->allows($auth->user(), 'system.audit')) {
                return new Response('Forbidden', 403);
            }

            $content = $view->render('system/audit.php', [
                'entries' => $audit->recent(50),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Audit Log',
                'content' => $content,
                'menu' => require BASE_PATH . '/config/menu.php',
                'user' => $auth->user(),
                'theme' => $theme->current(),
            ]));
        });

        $router->get('/system/updates', static function () use ($view, $auth, $theme, $gate): Response {
            if (!$gate->allows($auth->user(), 'system.updates')) {
                return new Response('Forbidden', 403);
            }

            $content = $view->render('system/updates.php', [
                'csrf' => Csrf::token(),
                'backups' => listCoreBackups(),
            ]);

            return new Response($view->render('layout.php', [
                'title' => 'Core Updates',
                'content' => $content,
                'menu' => require BASE_PATH . '/config/menu.php',
                'user' => $auth->user(),
                'theme' => $theme->current(),
            ]));
        });

        $router->post('/system/updates', static function (Request $request) use ($auth, $gate, $audit): Response {
            if (!$gate->allows($auth->user(), 'system.updates')) {
                return new Response('Forbidden', 403);
            }

            if (!Csrf::validate($request->input('csrf'))) {
                return new Response('Invalid CSRF token', 400);
            }

            try {
                $action = $request->input('action', 'update');
                if ($action === 'rollback') {
                    $backup = (string) $request->input('backup', '');
                    restoreCoreBackup($backup);
                    $audit->log('core.rollback', ['backup' => $backup, 'user' => $auth->user()['username'] ?? '']);
                } else {
                    $file = $_FILES['core_zip'] ?? null;
                    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                        return new Response('Upload fehlgeschlagen.', 400);
                    }

                    applyCoreUpdate($file['tmp_name']);
                    $audit->log('core.updated', ['user' => $auth->user()['username'] ?? '']);
                }
            } catch (\Throwable $exception) {
                return new Response('Update fehlgeschlagen: ' . $exception->getMessage(), 400);
            }

            return Response::redirect('/system/updates');
        });

        $router->post('/automation/callback', static function (Request $request): Response {
            return handleAutomationCallback($request);
        });

        return $router->dispatch($request);
    }
}

function handleAutomationCallback(Request $request): Response
{
    $storage = new AutomationStorage(BASE_PATH . '/storage/automation');
    $mailStorage = new MailStorage(BASE_PATH . '/storage/mail');
    $settings = $storage->settings();
    $token = $request->server['HTTP_X_ZCC_TOKEN'] ?? '';
    if ($token === '' || !hash_equals((string) ($settings['shared_secret'] ?? ''), $token)) {
        return new Response('Unauthorized', 401);
    }

    $payload = json_decode(file_get_contents('php://input') ?: '', true);
    if (!is_array($payload)) {
        return new Response('Invalid payload', 400);
    }

    $requestId = (string) ($payload['request_id'] ?? '');
    $run = $payload['run'] ?? [];
    if ($requestId === '' || !is_array($run)) {
        return new Response('Invalid payload', 400);
    }

    $storage->updateRun($requestId, [
        'request_id' => $requestId,
        'workflow_key' => $run['workflow_key'] ?? '',
        'status' => $run['status'] ?? '',
        'started_at' => $run['started_at'] ?? '',
        'finished_at' => $run['finished_at'] ?? '',
        'execution_id' => $run['execution_id'] ?? '',
        'error' => $run['error'] ?? null,
    ]);

    $updates = $payload['updates'] ?? [];
    if (is_array($updates)) {
        foreach ($updates as $update) {
            if (!is_array($update)) {
                continue;
            }
            $entity = $update['entity'] ?? [];
            $patch = $update['patch'] ?? [];
            if (($entity['type'] ?? '') === 'mail_draft' && is_array($patch)) {
                $mailStorage->saveDraft([
                    'id' => (string) ($entity['id'] ?? $requestId),
                    'subject' => $patch['subject'] ?? '',
                    'body_text' => $patch['body_text'] ?? '',
                    'body_html' => $patch['body_html'] ?? '',
                    'updated_at' => date(DATE_ATOM),
                ]);
            }
        }
    }

    return new Response('OK', 200);
}

function applyCoreUpdate(string $zipPath): void
{
    $zip = new \ZipArchive();
    if ($zip->open($zipPath) !== true) {
        throw new \RuntimeException('ZIP konnte nicht geöffnet werden.');
    }

    $backupPath = BASE_PATH . '/storage/core-backups/core-' . date('Ymd-His') . '.zip';
    $backup = new \ZipArchive();
    if ($backup->open($backupPath, \ZipArchive::CREATE) !== true) {
        $zip->close();
        throw new \RuntimeException('Backup konnte nicht erstellt werden.');
    }

    $whitelist = [
        'vendor-lite.php',
        'm.php',
    ];

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === null) {
            continue;
        }

        if (str_starts_with($name, 'app/Core/') && str_ends_with($name, '.php')) {
            $whitelist[] = $name;
        }
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === null || str_ends_with($name, '/')) {
            continue;
        }

        if (!in_array($name, $whitelist, true)) {
            $zip->close();
            $backup->close();
            throw new \RuntimeException('Datei nicht erlaubt: ' . $name);
        }

        $target = BASE_PATH . '/' . $name;
        if (is_file($target)) {
            $backup->addFile($target, $name);
        }
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === null || str_ends_with($name, '/')) {
            continue;
        }

        if (!in_array($name, $whitelist, true)) {
            continue;
        }

        $contents = $zip->getFromIndex($i);
        if ($contents === false) {
            $zip->close();
            $backup->close();
            throw new \RuntimeException('Konnte Datei nicht lesen: ' . $name);
        }

        $target = BASE_PATH . '/' . $name;
        $dir = dirname($target);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $zip->close();
            $backup->close();
            throw new \RuntimeException('Konnte Zielordner nicht erstellen.');
        }

        if (file_put_contents($target, $contents) === false) {
            $zip->close();
            $backup->close();
            throw new \RuntimeException('Konnte Datei nicht schreiben: ' . $name);
        }
    }

    $backup->close();
    $zip->close();
}

function listCoreBackups(): array
{
    $path = BASE_PATH . '/storage/core-backups';
    if (!is_dir($path)) {
        return [];
    }

    $files = glob($path . '/*.zip') ?: [];
    rsort($files);
    return array_map('basename', $files);
}

function restoreCoreBackup(string $file): void
{
    $backupPath = BASE_PATH . '/storage/core-backups/' . basename($file);
    if (!is_file($backupPath)) {
        throw new \RuntimeException('Backup nicht gefunden.');
    }

    $zip = new \ZipArchive();
    if ($zip->open($backupPath) !== true) {
        throw new \RuntimeException('Backup ZIP konnte nicht geöffnet werden.');
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if ($name === null || str_ends_with($name, '/')) {
            continue;
        }

        $contents = $zip->getFromIndex($i);
        if ($contents === false) {
            $zip->close();
            throw new \RuntimeException('Konnte Backup-Datei nicht lesen: ' . $name);
        }

        $target = BASE_PATH . '/' . $name;
        $dir = dirname($target);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $zip->close();
            throw new \RuntimeException('Konnte Zielordner nicht erstellen.');
        }

        if (file_put_contents($target, $contents) === false) {
            $zip->close();
            throw new \RuntimeException('Konnte Datei nicht schreiben: ' . $name);
        }
    }

    $zip->close();
}
use Zcc\Core\Mail\MailStorage;
