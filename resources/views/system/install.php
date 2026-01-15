<?php
/** @var string $csrf */
/** @var array $errors */
/** @var string|null $db_status */
?>
<div class="card card--narrow">
    <h2>ZCC Installer</h2>
    <?php if ($db_status): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($db_status, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/install.php" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
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
        <label class="field">
            <span>Admin Username</span>
            <input type="text" name="admin_username" required>
        </label>
        <label class="field">
            <span>Admin Password</span>
            <input type="password" name="admin_password" required>
        </label>
        <button type="submit" class="button">Installieren</button>
    </form>
</div>
