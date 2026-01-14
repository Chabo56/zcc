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
    <h2>Nextcloud Extended</h2>
    <p class="muted">Placeholder für WebDAV-Browser mit Root-Schutz.</p>
    <ul class="list">
        <li>Ordner-Browser</li>
        <li>Uploads & Downloads</li>
        <li>Root-Struktur /ZenityDent</li>
        <li>File Picker API (später)</li>
    </ul>
</div>
HTML;

echo $view->render('layout.php', [
    'title' => 'Nextcloud',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
