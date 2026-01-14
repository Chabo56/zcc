<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$config = require __DIR__ . '/../../bootstrap.php';
$auth = new AuthManager($config['auth']);

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$view = new View(__DIR__ . '/../../resources/views');
$theme = new ThemeManager();

$content = <<<HTML
<div class="card">
    <h2>Mail Center</h2>
    <p class="muted">Placeholder für Header-only Sync, Drafts und KI Compose via n8n.</p>
    <ul class="list">
        <li>Inbox (Header Cache)</li>
        <li>Compose/Reply (n8n)</li>
        <li>Drafts Freigabe</li>
        <li>Settings (IMAP/SMTP, Throttling)</li>
    </ul>
</div>
HTML;

echo $view->render('layout.php', [
    'title' => 'Mail',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
