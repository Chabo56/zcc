<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$config = require __DIR__ . '/../../bootstrap.php';
$settings = new SettingsRepository(__DIR__ . '/../../storage/nextcloud.json');
$auth = new AuthManager($config['auth'], new SettingsRepository(__DIR__ . '/../../storage/settings.json'));

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$view = new View(__DIR__ . '/../../resources/views');
$theme = new ThemeManager();

$errors = [];
$success = null;

$root = (string) $settings->get('root', '/ZenityDent');
$baseUrl = (string) $settings->get('base_url', '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'settings') {
            $settings->set('base_url', trim((string) ($_POST['base_url'] ?? '')));
            $settings->set('username', trim((string) ($_POST['username'] ?? '')));
            $settings->set('password', trim((string) ($_POST['password'] ?? '')));
            $settings->set('root', trim((string) ($_POST['root'] ?? '/ZenityDent')));
            $success = 'Settings gespeichert.';
        }
        if ($action === 'mkdir') {
            $basePath = normalizePath($root, (string) ($_POST['path'] ?? ''));
            $folder = trim((string) ($_POST['folder_name'] ?? ''));
            $path = normalizePath($root, $basePath . '/' . $folder);
            $response = webdavRequest($settings, 'MKCOL', $path);
            if ($response['status'] >= 200 && $response['status'] < 300) {
                $success = 'Ordner erstellt.';
            } else {
                $errors[] = 'MKCOL fehlgeschlagen (' . $response['status'] . ').';
            }
        }
        if ($action === 'upload' && isset($_FILES['upload'])) {
            $path = normalizePath($root, (string) ($_POST['path'] ?? ''));
            $file = $_FILES['upload'];
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $target = rtrim($path, '/') . '/' . basename((string) $file['name']);
                $response = webdavRequest($settings, 'PUT', $target, file_get_contents($file['tmp_name']));
                if ($response['status'] >= 200 && $response['status'] < 300) {
                    $success = 'Upload abgeschlossen.';
                } else {
                    $errors[] = 'Upload fehlgeschlagen (' . $response['status'] . ').';
                }
            } else {
                $errors[] = 'Upload fehlgeschlagen.';
            }
        }
    }
}

$browsePath = normalizePath($root, (string) ($_GET['path'] ?? $root));
$listing = webdavList($settings, $browsePath, $errors);

$content = $view->render('modules/nextcloud.php', [
    'csrf' => Csrf::token(),
    'errors' => $errors,
    'success' => $success,
    'listing' => $listing,
    'settings' => [
        'base_url' => $settings->get('base_url', ''),
        'username' => $settings->get('username', ''),
        'password' => $settings->get('password', ''),
        'root' => $root,
    ],
    'path' => $browsePath,
]);

echo $view->render('layout.php', [
    'title' => 'Nextcloud',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);

function normalizePath(string $root, string $path): string
{
    $root = '/' . trim($root, '/');
    $path = '/' . trim($path, '/');
    $full = str_starts_with($path, $root) ? $path : $root . ($path === '/' ? '' : $path);
    $full = preg_replace('#/+#', '/', $full) ?? $full;
    if (!str_starts_with($full, $root)) {
        return $root;
    }
    if (str_contains($full, '..')) {
        return $root;
    }
    return $full;
}

function webdavList(SettingsRepository $settings, string $path, array &$errors): array
{
    $response = webdavRequest($settings, 'PROPFIND', $path, null, ['Depth: 1']);
    if ($response['status'] >= 400) {
        $errors[] = 'WebDAV Fehler (' . $response['status'] . ').';
        return [];
    }

    $items = [];
    $xml = simplexml_load_string($response['body']);
    if (!$xml) {
        return [];
    }

    $xml->registerXPathNamespace('d', 'DAV:');
    foreach ($xml->xpath('//d:response') as $responseNode) {
        $href = (string) $responseNode->href;
        $name = basename(urldecode(trim($href, '/')));
        if ($name === '') {
            continue;
        }
        $isDir = !empty($responseNode->xpath('d:propstat/d:prop/d:resourcetype/d:collection'));
        $items[] = [
            'name' => $name,
            'path' => trim($path, '/') . '/' . $name,
            'type' => $isDir ? 'dir' : 'file',
        ];
    }

    return $items;
}

function webdavRequest(SettingsRepository $settings, string $method, string $path, ?string $body = null, array $headers = []): array
{
    $baseUrl = rtrim((string) $settings->get('base_url', ''), '/');
    if ($baseUrl === '') {
        return ['status' => 400, 'body' => 'Missing base_url'];
    }

    $url = $baseUrl . $path;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, (string) $settings->get('username', '') . ':' . (string) $settings->get('password', ''));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Type: application/octet-stream']));

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }

    $result = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $status,
        'body' => is_string($result) ? $result : '',
    ];
}
