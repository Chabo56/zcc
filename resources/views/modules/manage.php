<?php
/** @var array $modules */
/** @var string $csrf */
/** @var array $errors */
/** @var string|null $success */
?>
<div class="card">
    <h2>Module verwalten</h2>
    <p class="muted">ZIP-Upload installiert Module und ergänzt die Registry.</p>

    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>

    <form method="post" action="/modules.php" enctype="multipart/form-data" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="upload">
        <label class="field">
            <span>Module ZIP</span>
            <input type="file" name="module_zip" accept=".zip" required>
        </label>
        <label class="field field--inline">
            <input type="checkbox" name="enable_after" value="1">
            <span>Modul direkt aktivieren</span>
        </label>
        <button type="submit" class="button">ZIP installieren</button>
    </form>
</div>

<div class="card">
    <h3>Registrierte Module</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Key</th>
                <th>Name</th>
                <th>Version</th>
                <th>Status</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($modules)): ?>
                <tr>
                    <td colspan="5" class="muted">Keine Module registriert.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($modules as $module): ?>
                <?php $enabled = (bool) ($module['enabled'] ?? false); ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($module['key'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($module['name'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($module['version'] ?? ''), ENT_QUOTES); ?></td>
                    <td>
                        <span class="status <?php echo $enabled ? 'status--on' : 'status--off'; ?>">
                            <?php echo $enabled ? 'aktiv' : 'inaktiv'; ?>
                        </span>
                    </td>
                    <td>
                        <form method="post" action="/modules.php" class="inline-form">
                            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="key" value="<?php echo htmlspecialchars((string) ($module['key'] ?? ''), ENT_QUOTES); ?>">
                            <input type="hidden" name="enabled" value="<?php echo $enabled ? '0' : '1'; ?>">
                            <button type="submit" class="button button--ghost">
                                <?php echo $enabled ? 'Deaktivieren' : 'Aktivieren'; ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
