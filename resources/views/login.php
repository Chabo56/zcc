<?php
/** @var string $csrf */
$error = isset($_GET['error']);
?>
<div class="card card--narrow">
    <h2>Login</h2>
    <?php if ($error): ?>
        <p class="alert">Login fehlgeschlagen. Bitte prüfen.</p>
    <?php endif; ?>
    <form method="post" action="/login" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <label class="field">
            <span>Benutzername</span>
            <input type="text" name="username" required>
        </label>
        <label class="field">
            <span>Passwort</span>
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="button">Login</button>
    </form>
    <p class="muted">Default: admin / admin</p>
</div>
