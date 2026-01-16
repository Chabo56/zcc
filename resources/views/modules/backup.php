<?php
/** @var string $csrf */
/** @var array $errors */
/** @var string|null $success */
/** @var array $backups */
?>
<div class="card">
    <h2>Backup Manager</h2>
    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/m.php?m=backup-manager" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="run">
        <button type="submit" class="button">Backup jetzt erstellen</button>
    </form>
    <p class="muted">Uploads gehen nach <code>/ZenityDent/backups/db/YYYY-MM-DD</code> im Nextcloud WebDAV.</p>
</div>

<div class="card">
    <h2>Lokale Backups</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Datei</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($backups)): ?>
                <tr>
                    <td class="muted">Keine Backups vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($backups as $backup): ?>
                <tr>
                    <td><?php echo htmlspecialchars($backup, ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
