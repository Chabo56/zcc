<?php
/** @var string $csrf */
/** @var array $errors */
/** @var string|null $success */
/** @var array $listing */
/** @var array $settings */
/** @var string $path */
?>
<div class="card">
    <h2>Nextcloud Settings</h2>
    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/m.php?m=nextcloud-center" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="settings">
        <label class="field">
            <span>Base URL (WebDAV)</span>
            <input type="text" name="base_url" value="<?php echo htmlspecialchars((string) ($settings['base_url'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Username</span>
            <input type="text" name="username" value="<?php echo htmlspecialchars((string) ($settings['username'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Password</span>
            <input type="password" name="password" value="<?php echo htmlspecialchars((string) ($settings['password'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Root Path</span>
            <input type="text" name="root" value="<?php echo htmlspecialchars((string) ($settings['root'] ?? '/ZenityDent'), ENT_QUOTES); ?>">
        </label>
        <button type="submit" class="button">Settings speichern</button>
    </form>
</div>

<div class="card">
    <h2>Browser</h2>
    <p class="muted">Aktueller Pfad: <?php echo htmlspecialchars($path, ENT_QUOTES); ?></p>
    <div class="module-actions">
        <form method="post" action="/m.php?m=nextcloud-center" class="inline-form">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
            <input type="hidden" name="action" value="mkdir">
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path, ENT_QUOTES); ?>">
            <input type="text" name="folder_name" placeholder="Ordnername" required>
            <button type="submit" class="button button--ghost">Ordner erstellen</button>
        </form>
        <form method="post" action="/m.php?m=nextcloud-center" enctype="multipart/form-data" class="inline-form">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="path" value="<?php echo htmlspecialchars($path, ENT_QUOTES); ?>">
            <input type="file" name="upload" required>
            <button type="submit" class="button button--ghost">Upload</button>
        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Typ</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($listing)): ?>
                <tr>
                    <td colspan="3" class="muted">Keine Einträge.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($listing as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name'], ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars($item['type'], ENT_QUOTES); ?></td>
                    <td>
                        <?php if ($item['type'] === 'dir'): ?>
                            <a class="button button--ghost" href="/m.php?m=nextcloud-center&path=<?php echo urlencode($item['path']); ?>">Öffnen</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
