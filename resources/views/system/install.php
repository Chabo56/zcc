<?php
/** @var string $csrf */
/** @var array $errors */
/** @var string|null $db_status */
/** @var string|null $schema_status */
/** @var int $step */
?>
<div class="card card--narrow">
    <h2>ZCC Installer</h2>
    <p class="muted">Schritt <?php echo (int) $step; ?> von 3</p>
    <?php if ($db_status): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($db_status, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php if ($schema_status): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($schema_status, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/install.php?step=<?php echo (int) $step; ?>" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <?php if ($step === 1): ?>
            <label class="field">
                <span>DB Host</span>
                <input type="text" name="db_host" placeholder="localhost" required>
            </label>
            <label class="field">
                <span>DB Name</span>
                <input type="text" name="db_name" required>
            </label>
            <label class="field">
                <span>DB User</span>
                <input type="text" name="db_user" required>
            </label>
            <label class="field">
                <span>DB Password</span>
                <input type="password" name="db_pass">
            </label>
            <button type="submit" class="button">Weiter zu Schritt 2</button>
        <?php elseif ($step === 2): ?>
            <label class="field field--inline">
                <input type="checkbox" name="apply_schema" value="1" checked>
                <span>Schema anwenden (storage/schema.sql)</span>
            </label>
            <label class="field field--inline">
                <input type="checkbox" name="seed_modules" value="1" checked>
                <span>Module in DB seeden (storage/modules.json)</span>
            </label>
            <button type="submit" class="button">Weiter zu Schritt 3</button>
        <?php else: ?>
            <label class="field">
                <span>Admin Username</span>
                <input type="text" name="admin_username" required>
            </label>
            <label class="field">
                <span>Admin Password</span>
                <input type="password" name="admin_password" required>
            </label>
            <button type="submit" class="button">Installation abschließen</button>
        <?php endif; ?>
    </form>
</div>
