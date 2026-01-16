<?php
/** @var string $title */
/** @var string $content */
/** @var array $menu */
/** @var array|null $user */
/** @var string $theme */
?>
<!DOCTYPE html>
<html lang="de" data-theme="<?php echo htmlspecialchars($theme, ENT_QUOTES); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($title, ENT_QUOTES); ?> · ZCC</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body>
    <div class="app-shell">
        <aside class="sidebar">
            <div class="sidebar__brand">
                <span class="brand">ZCC</span>
                <span class="subtitle">ZenityDent Control Center</span>
            </div>
            <nav class="sidebar__nav">
                <?php foreach ($menu as $item): ?>
                    <div class="nav-group">
                        <a class="nav-link" href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES); ?>">
                            <span><?php echo htmlspecialchars($item['title'], ENT_QUOTES); ?></span>
                        </a>
                        <?php if (!empty($item['submenu'])): ?>
                            <div class="submenu">
                                <?php foreach ($item['submenu'] as $child): ?>
                                    <a class="submenu__link" href="<?php echo htmlspecialchars($child['url'], ENT_QUOTES); ?>">
                                        <?php echo htmlspecialchars($child['title'], ENT_QUOTES); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </aside>
        <main class="main">
            <header class="topbar">
                <div>
                    <h1><?php echo htmlspecialchars($title, ENT_QUOTES); ?></h1>
                </div>
                <div class="topbar__actions">
                    <form method="post" action="/theme" class="inline-form">
                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(\Zcc\Core\Security\Csrf::token(), ENT_QUOTES); ?>">
                        <input type="hidden" name="theme" value="<?php echo $theme === 'dark' ? 'light' : 'dark'; ?>">
                        <button type="submit" class="button button--ghost">
                            Theme: <?php echo htmlspecialchars($theme, ENT_QUOTES); ?>
                        </button>
                    </form>
                    <?php if ($user): ?>
                        <form method="post" action="/logout" class="inline-form">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(\Zcc\Core\Security\Csrf::token(), ENT_QUOTES); ?>">
                            <button type="submit" class="button button--danger">Logout</button>
                        </form>
                    <?php else: ?>
                        <a class="button" href="/login">Login</a>
                    <?php endif; ?>
                </div>
            </header>
            <section class="content">
                <?php echo $content; ?>
            </section>
        </main>
    </div>
    <script src="/assets/app.js"></script>
</body>
</html>
