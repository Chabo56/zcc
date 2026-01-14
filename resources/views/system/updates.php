<?php
/** @var string $csrf */
/** @var array $backups */
?>
<div class="card">
    <h2>Core Updates</h2>
    <p class="muted">ZIP Upload erlaubt nur Whitelist-Dateien: vendor-lite.php, m.php, app/Core/*.php</p>
    <form method="post" action="/system/updates" enctype="multipart/form-data" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="update">
        <label class="field">
            <span>Core Update ZIP</span>
            <input type="file" name="core_zip" accept=".zip" required>
        </label>
        <button type="submit" class="button">Update einspielen</button>
    </form>
</div>

<div class="card">
    <h3>Rollback</h3>
    <form method="post" action="/system/updates" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="rollback">
        <label class="field">
            <span>Backup auswählen</span>
            <select name="backup">
                <?php foreach ($backups as $backup): ?>
                    <option value="<?php echo htmlspecialchars($backup, ENT_QUOTES); ?>"><?php echo htmlspecialchars($backup, ENT_QUOTES); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="button button--ghost">Rollback ausführen</button>
    </form>
</div>
