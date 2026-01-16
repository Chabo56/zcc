<?php
/** @var string $csrf */
/** @var array $settings */
/** @var array $index */
/** @var array $drafts */
/** @var array $requests */
/** @var array $errors */
/** @var string|null $success */
?>
<div class="card">
    <h2>Mail Settings</h2>
    <?php if ($success): ?>
        <p class="alert alert--success"><?php echo htmlspecialchars($success, ENT_QUOTES); ?></p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="alert"><?php echo htmlspecialchars($error, ENT_QUOTES); ?></p>
    <?php endforeach; ?>
    <form method="post" action="/m.php?m=mail-center" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="settings">
        <label class="field">
            <span>IMAP Host</span>
            <input type="text" name="imap_host" value="<?php echo htmlspecialchars((string) ($settings['imap_host'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>IMAP User</span>
            <input type="text" name="imap_user" value="<?php echo htmlspecialchars((string) ($settings['imap_user'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>IMAP Password</span>
            <input type="password" name="imap_password" value="<?php echo htmlspecialchars((string) ($settings['imap_password'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>SMTP Host</span>
            <input type="text" name="smtp_host" value="<?php echo htmlspecialchars((string) ($settings['smtp_host'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>n8n URL (Compose)</span>
            <input type="text" name="n8n_url" value="<?php echo htmlspecialchars((string) ($settings['n8n_url'] ?? ''), ENT_QUOTES); ?>">
        </label>
        <button type="submit" class="button">Settings speichern</button>
    </form>
</div>

<div class="card">
    <h2>Compose (KI)</h2>
    <form method="post" action="/m.php?m=mail-center" class="form">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
        <input type="hidden" name="action" value="compose">
        <label class="field">
            <span>Request ID</span>
            <input type="text" name="request_id" value="<?php echo htmlspecialchars((string) bin2hex(random_bytes(8)), ENT_QUOTES); ?>">
        </label>
        <label class="field">
            <span>Language</span>
            <select name="language">
                <option value="de">de</option>
                <option value="en">en</option>
                <option value="fr">fr</option>
                <option value="nl">nl</option>
            </select>
        </label>
        <label class="field">
            <span>Tone</span>
            <input type="text" name="tone" value="friendly">
        </label>
        <label class="field">
            <span>Goal</span>
            <input type="text" name="goal" value="reply">
        </label>
        <label class="field">
            <span>Stichpunkte</span>
            <textarea name="notes" rows="4"></textarea>
        </label>
        <button type="submit" class="button">Compose anfordern</button>
    </form>
</div>

<div class="card">
    <h2>Inbox (Header Cache)</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Von</th>
                <th>Betreff</th>
                <th>Datum</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($index)): ?>
                <tr>
                    <td colspan="3" class="muted">Keine Header im Cache.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($index as $mail): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($mail['from'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($mail['subject'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($mail['date'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Drafts</h2>
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Subject</th>
                <th>Updated</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($drafts)): ?>
                <tr>
                    <td colspan="3" class="muted">Keine Drafts vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach ($drafts as $draft): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($draft['id'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($draft['subject'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($draft['updated_at'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Compose Requests</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="3" class="muted">Keine Requests vorhanden.</td>
                </tr>
            <?php endif; ?>
            <?php foreach (array_reverse($requests) as $request): ?>
                <tr>
                    <td><?php echo htmlspecialchars((string) ($request['request_id'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($request['status'] ?? ''), ENT_QUOTES); ?></td>
                    <td><?php echo htmlspecialchars((string) ($request['created_at'] ?? ''), ENT_QUOTES); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
