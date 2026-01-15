<?php

declare(strict_types=1);

return [
    [
        'title' => 'Dashboard',
        'url' => '/',
        'group' => 'Dashboard',
        'icon' => 'home',
    ],
    [
        'title' => 'Automation',
        'url' => '#',
        'group' => 'Automation',
        'icon' => 'workflow',
        'submenu' => [
            ['title' => 'Workflows', 'url' => '/automation/workflows'],
            ['title' => 'Runs & Logs', 'url' => '/automation/runs'],
            ['title' => 'Events', 'url' => '/automation/events'],
            ['title' => 'Settings', 'url' => '/automation/settings'],
        ],
    ],
    [
        'title' => 'Mail',
        'url' => '#',
        'group' => 'Mail',
        'icon' => 'mail',
        'submenu' => [
            ['title' => 'Inbox', 'url' => '/mail/inbox'],
            ['title' => 'Compose (KI)', 'url' => '/mail/compose'],
            ['title' => 'Drafts', 'url' => '/mail/drafts'],
            ['title' => 'Settings', 'url' => '/mail/settings'],
        ],
    ],
    [
        'title' => 'Shopware',
        'url' => '#',
        'group' => 'Shopware',
        'icon' => 'cart',
        'submenu' => [
            ['title' => 'Overview', 'url' => '/shopware/overview'],
            ['title' => 'Orders', 'url' => '/shopware/orders'],
            ['title' => 'Metrics', 'url' => '/shopware/metrics'],
        ],
    ],
    [
        'title' => 'Nextcloud',
        'url' => '#',
        'group' => 'Nextcloud',
        'icon' => 'cloud',
        'submenu' => [
            ['title' => 'Browser', 'url' => '/nextcloud/browser'],
            ['title' => 'Uploads', 'url' => '/nextcloud/uploads'],
            ['title' => 'Settings', 'url' => '/nextcloud/settings'],
        ],
    ],
    [
        'title' => 'Business',
        'url' => '#',
        'group' => 'Business',
        'icon' => 'briefcase',
        'submenu' => [
            ['title' => 'Customers', 'url' => '/business/customers'],
            ['title' => 'Invoices', 'url' => '/business/invoices'],
            ['title' => 'Shipping', 'url' => '/business/shipping'],
        ],
    ],
    [
        'title' => 'System',
        'url' => '#',
        'group' => 'System',
        'icon' => 'settings',
        'submenu' => [
            ['title' => 'Users & Roles', 'url' => '/system/users'],
            ['title' => 'Permissions', 'url' => '/system/permissions'],
            ['title' => 'Audit Log', 'url' => '/system/audit'],
            ['title' => 'Modules', 'url' => '/modules.php'],
            ['title' => 'Backups', 'url' => '/m.php?m=backup-manager'],
            ['title' => 'Core Updates', 'url' => '/system/updates'],
            ['title' => 'Settings', 'url' => '/system/settings'],
        ],
    ],
];
