<?php
/** @var string $csrf */
/** @var array $settings */
/** @var array $workflows */
/** @var array $events */
/** @var array $runs */
/** @var array $errors */
/** @var string|null $success */
?>
<div class="card">
    <h2>Automation Settings</h2>
    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/m.php?m=automation-center" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="settings">
        <label class="field">
            <span>n8n Base URL</span>
            <input type="text" name="base_url" value="<?php echo htmlspecialchars((string) ($settings['base_url'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Shared Secret</span>
            <input type="text" name="shared_secret" value="<?php echo htmlspecialchars((string) ($settings['shared_secret'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Timeout (s)</span>
            <input type="number" name="timeout" value="<?php echo htmlspecialchars((string) ($settings['timeout'] ?? 30), ENT_QUOTES); ?>">
        </label>
        <button type="submit" class="button">Settings speichern</button>
    </form>
</div>

<div class="card">
    <h2>Workflows</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Key</th>
                <th>Name</th>
                <th>Version</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($workflows)): ?>
                <tr>
                    <td colspan="4" class="muted">Keine Workflows registriert.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($workflows as $workflow): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($workflow['key'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($workflow['name'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($workflow['version'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($workflow['status'] ?? 'unknown'), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Events</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Event</th>
                <th>Workflow</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($events)): ?>
                <tr>
                    <td colspan="2" class="muted">Keine Events registriert.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($events as $event): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($event['event'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($event['workflow_key'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Runs & Logs</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Workflow</th>
                <th>Status</th>
                <th>Started</th>
                <th>Finished</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($runs)): ?>
                <tr>
                    <td colspan="5" class="muted">Keine Runs vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach (array_reverse($runs) as $run): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($run['request_id'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($run['workflow_key'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($run['status'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($run['started_at'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($run['finished_at'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
