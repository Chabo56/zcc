<?php
/** @var array|null $user */
?>
<div class="card">
    <h2>Willkommen im ZCC</h2>
    <p>Dieses Grundgerüst zeigt die Core-Navigation, Theme-Toggle und Auth-Shell.</p>
    <ul class="list">
        <li>Core bleibt minimal, Module liefern die Fachlogik.</li>
        <li>Menüstruktur ist final gemäß Master-Prompt.</li>
        <li>CSRF-gesicherte Forms sind aktiv.</li>
    </ul>
    <?php if ($user): ?>
        <p class="muted">Angemeldet als <strong><?php echo htmlspecialchars($user['username'], ENT_QUOTES); ?></strong>.</p>
    <?php else: ?>
        <p class="muted">Bitte anmelden, um Module zu nutzen.</p>
    <?php endif; ?>
</div>
