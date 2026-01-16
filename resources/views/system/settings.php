<?php
/** @var string $csrf */
/** @var array $settings */
$admin = $settings['admin'] ?? ['username' => 'admin', 'password' => 'admin'];
?>
<div class="card">
    <h2>System Settings</h2>
    <form method="post" action="/system/settings" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <label class="field">
            <span>Base URL</span>
            <input type="text" name="base_url" value="<?php echo htmlspecialchars((string) ($settings['base_url'] ?? '/'), ENT_QUOTES); ?>">
        </label>
        <label class="field field--inline">
            <input type="checkbox" name="registration_enabled" value="1" <?php echo !empty($settings['registration_enabled']) ? 'checked' : ''; ?>>
            <span>Registrierung aktivieren</span>
        </label>
        <label class="field">
            <span>Default Theme</span>
            <select name="theme_default">
                <?php $themeDefault = (string) ($settings['theme_default'] ?? 'dark'); ?>
                <option value="dark" <?php echo $themeDefault === 'dark' ? 'selected' : ''; ?>>dark</option>
                <option value="light" <?php echo $themeDefault === 'light' ? 'selected' : ''; ?>>light</option>
            </select>
        </label>
        <label class="field">
            <span>Admin Username</span>
            <input type="text" name="admin_username" value="<?php echo htmlspecialchars((string) ($admin['username'] ?? 'admin'), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Admin Password</span>
            <input type="password" name="admin_password" value="<?php echo htmlspecialchars((string) ($admin['password'] ?? 'admin'), ENT_QUOTES); ?>">
        </label>
        <button type="submit" class="button">Settings speichern</button>
    </form>
</div>
