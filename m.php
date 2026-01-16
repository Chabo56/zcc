<?php

declare(strict_types=1);

$config = require __DIR__ . '/bootstrap.php';

use Zcc\Core\Modules\ModuleLoader;
use Zcc\Core\Modules\ModuleRegistry;
use Zcc\Core\Views\View;

$registry = new ModuleRegistry(__DIR__ . '/storage/modules.json');
$loader = new ModuleLoader(__DIR__ . '/modules', $registry);
$view = new View(__DIR__ . '/resources/views');

$key = $_GET['m'] ?? '';
$modulePath = $loader->resolve($key);

if (!$modulePath) {
    echo $view->render('layout.php', [
        'title' => 'Module disabled',
        'content' => $view->render('modules/disabled.php', ['key' => $key]),
        'menu' => $config['menu'],
        'user' => null,
        'theme' => 'dark',
    ]);
    exit;
}

require $modulePath;
