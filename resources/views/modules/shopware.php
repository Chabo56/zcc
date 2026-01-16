<?php
/** @var string $csrf */
/** @var array $settings */
/** @var array $metrics */
/** @var array $orders */
/** @var array $errors */
/** @var string|null $success */
?>
<div class="card">
    <h2>Shopware Settings</h2>
    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/m.php?m=shopware-center" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="settings">
        <label class="field">
            <span>n8n URL (Refresh)</span>
            <input type="text" name="n8n_url" value="<?php echo htmlspecialchars((string) ($settings['n8n_url'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <button type="submit" class="button">Settings speichern</button>
    </form>
</div>

<div class="card">
    <h2>Shopware Overview</h2>
    <div class="module-actions">
        <form method="post" action="/m.php?m=shopware-center" class="inline-form">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
            <input type="hidden" name="action" value="refresh">
            <button type="submit" class="button button--ghost">Refresh via n8n</button>
        </form>
        <span class="muted">Letztes Update: <?php echo htmlspecialchars((string) ($metrics['last_updated'] ?? '—'), ENT_QUOTES); ?></span>
    </div>
    <div class="stats">
        <div class="stat">
            <span class="stat__label">Bestellungen heute</span>
            <span class="stat__value"><?php echo htmlspecialchars((string) ($metrics['orders_today'] ?? 0), ENT_QUOTES); ?></span>
        </div>
        <div class="stat">
            <span class="stat__label">Umsatz heute</span>
            <span class="stat__value"><?php echo htmlspecialchars((string) ($metrics['revenue_today'] ?? 0), ENT_QUOTES); ?></span>
        </div>
    </div>
</div>

<div class="card">
    <h2>Letzte Bestellungen</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Kunde</th>
                <th>Betrag</th>
                <th>Zeit</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="5" class="muted">Keine Bestellungen vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($order['number'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($order['customer'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($order['amount'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($order['time'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($order['status'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
