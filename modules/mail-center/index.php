<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Mail\MailStorage;
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
$storage = new MailStorage(__DIR__ . '/../../storage/mail');

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf'] ?? null)) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        if ($action === 'settings') {
            $settings = [
                'imap_host' => trim((string) ($_POST['imap_host'] ?? '')),
                'imap_user' => trim((string) ($_POST['imap_user'] ?? '')),
                'imap_password' => trim((string) ($_POST['imap_password'] ?? '')),
                'smtp_host' => trim((string) ($_POST['smtp_host'] ?? '')),
                'n8n_url' => trim((string) ($_POST['n8n_url'] ?? '')),
            ];
            $storage->saveSettings($settings);
            $success = 'Settings gespeichert.';
        }

        if ($action === 'compose') {
            $requestId = trim((string) ($_POST['request_id'] ?? ''));
            if ($requestId === '') {
                $errors[] = 'Request ID fehlt.';
            } else {
                $request = [
                    'request_id' => $requestId,
                    'status' => 'queued',
                    'created_at' => date(DATE_ATOM),
                    'payload' => [
                        'event' => 'mail.compose.requested',
                        'request_id' => $requestId,
                        'idempotency_key' => $requestId,
                        'requested_by' => [
                            'user_id' => 1,
                            'role' => $auth->user()['role'] ?? 'admin',
                        ],
                        'language' => (string) ($_POST['language'] ?? 'de'),
                        'entity' => [
                            'type' => 'mail',
                            'id' => $requestId,
                        ],
                        'meta' => [
                            'tone' => (string) ($_POST['tone'] ?? 'friendly'),
                            'goal' => (string) ($_POST['goal'] ?? 'reply'),
                        ],
                        'data' => [
                            'notes' => (string) ($_POST['notes'] ?? ''),
                        ],
                    ],
                ];

                $storage->appendRequest($request);
                $success = 'Compose Request gespeichert.';

                $settings = $storage->settings();
                if (!empty($settings['n8n_url'])) {
                    $result = sendToN8n($settings['n8n_url'], $request['payload']);
                    if ($result !== true) {
                        $errors[] = $result;
                    } else {
                        $success = 'Compose Request an n8n gesendet.';
                    }
                }
            }
        }
    }
}

$content = $view->render('modules/mail.php', [
    'csrf' => Csrf::token(),
    'settings' => $storage->settings(),
    'index' => $storage->index(),
    'drafts' => $storage->drafts(),
    'requests' => $storage->requests(),
    'errors' => $errors,
    'success' => $success,
]);

echo $view->render('layout.php', [
    'title' => 'Mail',
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
