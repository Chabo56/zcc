<?php

declare(strict_types=1);

use Zcc\Core\Auth\AuthManager;
use Zcc\Core\Settings\SettingsRepository;
use Zcc\Core\Database\DbConnector;
use Zcc\Core\Theme\ThemeManager;
use Zcc\Core\Views\View;

$config = require __DIR__ . '/../../bootstrap.php';
$auth = new AuthManager($config['auth'], new SettingsRepository(__DIR__ . '/../../storage/settings.json', new DbConnector(__DIR__ . '/../../storage/db.json')));

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$view = new View(__DIR__ . '/../../resources/views');
$theme = new ThemeManager();

$content = <<<HTML
<div class="card">
    <h2>Business Light</h2>
    <p class="muted">Placeholder für Kunden, Rechnungen und Versandstatus (Registry-only).</p>
    <ul class="list">
        <li>Customers (Name, Email, Nextcloud Folder)</li>
        <li>Invoices Registry</li>
        <li>Shipping Status</li>
    </ul>
</div>
HTML;

echo $view->render('layout.php', [
    'title' => 'Business',
    'content' => $content,
    'menu' => $config['menu'],
    'user' => $auth->user(),
    'theme' => $theme->current(),
]);
