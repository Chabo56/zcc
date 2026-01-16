<?php
/** @var array $entries */
?>
<div class="card">
    <h2>Audit Log</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Zeit</th>
                <th>Aktion</th>
                <th>Kontext</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entries)): ?>
                <tr>
                    <td colspan="3" class="muted">Keine Einträge vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($entry['time'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($entry['action'] ?? ''), ENT_QUOTES); ?></td>
                    <td><pre><?php echo htmlspecialchars(json_encode($entry['context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), ENT_QUOTES); ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
