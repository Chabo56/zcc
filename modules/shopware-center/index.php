<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Shopware\ShopwareStorage;
use Zcc\Core\Security\Csrf;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$config = require __DIR__ . '/../../bootstrap.php';
$auth = new AuthManager($config['auth'], new SettingsRepository(__DIR__ . '/../../storage/settings.json'));

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$view = new View(__DIR__ . '/../../resources/views');
$theme = new ThemeManager();
$storage = new ShopwareStorage(__DIR__ . '/../../storage/shopware');

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'settings') {
            $storage->saveSettings([
                'n8n_url' => trim((string) ($_POST['n8n_url'] ?? '')),
            ]);
            $success = 'Settings gespeichert.';
        }

        if ($action === 'refresh') {
            $settings = $storage->settings();
            if (empty($settings['n8n_url'])) {
                $errors[] = 'n8n URL fehlt.';
            } else {
                $result = sendToN8n($settings['n8n_url'], ['event' => 'shopware.refresh']);
                if ($result !== true) {
                    $errors[] = $result;
                } else {
                    $success = 'Refresh an n8n gesendet.';
                }
            }
        }
    }
}

$content = $view->render('modules/shopware.php', [
    'csrf' => Csrf::token(),
    'settings' => $storage->settings(),
    'metrics' => $storage->metrics(),
    'orders' => $storage->orders(),
    'errors' => $errors,
    'success' => $success,
]);

echo $view->render('layout.php', [
    'title' => 'Shopware',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);

function sendToN8n(string $url, array $payload): true|string
{
    $ch = curl_init($url);
    if (!$ch) {
        return 'n8n Request konnte nicht initialisiert werden.';
    }

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_SLASHES));
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        return 'n8n Request fehlgeschlagen (' . $status . ').';
    }

    return true;
}
