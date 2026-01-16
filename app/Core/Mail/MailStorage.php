<?php

declare(strict_types=1);

namespace Zcc\Core\Mail;

final class MailStorage
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly string $basePath, ?\Zcc\Core\Database\DbConnector $connector = null)
    {
        if ($connector && $connector->isConfigured()) {
            try {
                $this->pdo = $connector->connect();
            } catch (\Throwable) {
                $this->pdo = null;
            }
        }
    }

    public function settings(): array
    {
        return $this->readJson($this->basePath . '/settings.json', [
            'imap_host' => '',
            'imap_user' => '',
            'imap_password' => '',
            'smtp_host' => '',
            'n8n_url' => '',
        ]);
    }

    public function saveSettings(array $settings): void
    {
        $this->writeJson($this->basePath . '/settings.json', $settings);
    }

    public function index(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT sender AS `from`, subject, received_at AS `date` FROM mail_index ORDER BY received_at DESC LIMIT 50');
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return $this->readJson($this->basePath . '/index.json', []);
    }

    public function saveIndex(array $items): void
    {
        if ($this->pdo) {
            $this->pdo->beginTransaction();
            $this->pdo->exec('DELETE FROM mail_index');
            $stmt = $this->pdo->prepare('INSERT INTO mail_index (uid, sender, subject, received_at) VALUES (:uid, :sender, :subject, :received_at)');
            foreach ($items as $item) {
                $stmt->execute([
                    'uid' => $item['uid'] ?? '',
                    'sender' => $item['from'] ?? '',
                    'subject' => $item['subject'] ?? '',
                    'received_at' => $item['date'] ?? null,
                ]);
            }
            $this->pdo->commit();
            return;
        }

        $this->writeJson($this->basePath . '/index.json', $items);
    }

    public function drafts(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT draft_id AS id, subject, updated_at FROM mail_drafts ORDER BY updated_at DESC LIMIT 50');
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return $this->readJson($this->basePath . '/drafts.json', []);
    }

    public function saveDraft(array $draft): void
    {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare('INSERT INTO mail_drafts (draft_id, subject, body_text, body_html, updated_at) VALUES (:id, :subject, :body_text, :body_html, :updated_at) ON DUPLICATE KEY UPDATE subject = VALUES(subject), body_text = VALUES(body_text), body_html = VALUES(body_html), updated_at = VALUES(updated_at)');
            $stmt->execute([
                'id' => $draft['id'] ?? '',
                'subject' => $draft['subject'] ?? '',
                'body_text' => $draft['body_text'] ?? null,
                'body_html' => $draft['body_html'] ?? null,
                'updated_at' => $draft['updated_at'] ?? null,
            ]);
            return;
        }

        $drafts = $this->drafts();
        $id = $draft['id'] ?? null;
        if ($id) {
            foreach ($drafts as $i => $entry) {
                if (($entry['id'] ?? '') === $id) {
                    $drafts[$i] = array_merge($entry, $draft);
                    $this->writeJson($this->basePath . '/drafts.json', $drafts);
                    return;
                }
            }
        }

        $drafts[] = $draft;
        $this->writeJson($this->basePath . '/drafts.json', $drafts);
    }

    public function requests(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT request_id, status, created_at FROM mail_requests ORDER BY created_at DESC LIMIT 50');
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return $this->readJson($this->basePath . '/requests.json', []);
    }

    public function appendRequest(array $request): void
    {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare('INSERT INTO mail_requests (request_id, status, payload, created_at) VALUES (:request_id, :status, :payload, :created_at)');
            $stmt->execute([
                'request_id' => $request['request_id'] ?? '',
                'status' => $request['status'] ?? '',
                'payload' => json_encode($request['payload'] ?? [], JSON_UNESCAPED_SLASHES),
                'created_at' => $request['created_at'] ?? null,
            ]);
            return;
        }

        $requests = $this->requests();
        $requests[] = $request;
        $this->writeJson($this->basePath . '/requests.json', $requests);
    }

    private function readJson(string $path, array $default): array
    {
        if (!is_file($path)) {
            return $default;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $default;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : $default;
    }

    private function writeJson(string $path, array $payload): void
    {
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new \RuntimeException('Failed to encode mail data.');
        }

        if (file_put_contents($path, $encoded) === false) {
            throw new \RuntimeException('Failed to write mail data.');
        }
    }
}
