<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
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

$content = <<<HTML
<div class="card">
    <h2>Shopware Overview</h2>
    <p class="muted">Placeholder für Dashboard-Widget mit Cache-Daten aus n8n.</p>
    <ul class="list">
        <li>Bestellungen heute</li>
        <li>Umsatz heute</li>
        <li>Letzte 5 Bestellungen</li>
        <li>Refresh-Trigger via n8n</li>
    </ul>
</div>
HTML;

echo $view->render('layout.php', [
    'title' => 'Shopware',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
