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
    <h2>Automation Center</h2>
    <p class="muted">Placeholder für Workflows, Runs & Logs, Events und Settings (n8n-first).</p>
    <ul class="list">
        <li>Workflows Registry (readonly)</li>
        <li>Runs & Logs Übersicht</li>
        <li>Events Mapping</li>
        <li>Settings (Base URL, Shared Secret)</li>
    </ul>
</div>
HTML;

echo $view->render('layout.php', [
    'title' => 'Automation',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
