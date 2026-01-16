<?php

declare(strict_types=1);

return [
    'app_name' => 'ZenityDent Control Center',
    'base_url' => '/',
    'security' => [
        'session_name' => 'zcc_session',
    ],
    'auth' => [
        'default_admin' => [
            'username' => 'admin',
            'password' => 'admin',
        ],
    ],
    'menu' => require __DIR__ . '/menu.php',
];
