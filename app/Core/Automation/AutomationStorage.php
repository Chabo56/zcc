<?php

declare(strict_types=1);

namespace Zcc\Core\Automation;

final class AutomationStorage
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
            'base_url' => '',
            'shared_secret' => bin2hex(random_bytes(16)),
            'timeout' => 30,
        ]);
    }

    public function saveSettings(array $settings): void
    {
        $this->writeJson($this->basePath . '/settings.json', $settings);
    }

    public function workflows(): array
    {
        return $this->readJson($this->basePath . '/workflows.json', []);
    }

    public function saveWorkflows(array $workflows): void
    {
        $this->writeJson($this->basePath . '/workflows.json', $workflows);
    }

    public function events(): array
    {
        return $this->readJson($this->basePath . '/events.json', []);
    }

    public function saveEvents(array $events): void
    {
        $this->writeJson($this->basePath . '/events.json', $events);
    }

    public function runs(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT request_id, workflow_key, status, started_at, finished_at, execution_id, error FROM automation_runs ORDER BY id DESC LIMIT 100');
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return $this->readJson($this->basePath . '/runs.json', []);
    }

    public function appendRun(array $run): void
    {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare('INSERT INTO automation_runs (request_id, workflow_key, status, started_at, finished_at, execution_id, error) VALUES (:request_id, :workflow_key, :status, :started_at, :finished_at, :execution_id, :error)');
            $stmt->execute([
                'request_id' => $run['request_id'] ?? '',
                'workflow_key' => $run['workflow_key'] ?? '',
                'status' => $run['status'] ?? '',
                'started_at' => $run['started_at'] ?? null,
                'finished_at' => $run['finished_at'] ?? null,
                'execution_id' => $run['execution_id'] ?? null,
                'error' => json_encode($run['error'] ?? null),
            ]);
            return;
        }

        $runs = $this->runs();
        $runs[] = $run;
        $this->writeJson($this->basePath . '/runs.json', $runs);
    }

    public function updateRun(string $requestId, array $update): void
    {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare('INSERT INTO automation_runs (request_id, workflow_key, status, started_at, finished_at, execution_id, error) VALUES (:request_id, :workflow_key, :status, :started_at, :finished_at, :execution_id, :error) ON DUPLICATE KEY UPDATE workflow_key = VALUES(workflow_key), status = VALUES(status), started_at = VALUES(started_at), finished_at = VALUES(finished_at), execution_id = VALUES(execution_id), error = VALUES(error)');
            $stmt->execute([
                'request_id' => $requestId,
                'workflow_key' => $update['workflow_key'] ?? '',
                'status' => $update['status'] ?? '',
                'started_at' => $update['started_at'] ?? null,
                'finished_at' => $update['finished_at'] ?? null,
                'execution_id' => $update['execution_id'] ?? null,
                'error' => json_encode($update['error'] ?? null),
            ]);
            return;
        }

        $runs = $this->runs();
        foreach ($runs as $index => $entry) {
            if (($entry['request_id'] ?? '') === $requestId) {
                $runs[$index] = array_merge($entry, $update);
                $this->writeJson($this->basePath . '/runs.json', $runs);
                return;
            }
        }

        $update['request_id'] = $requestId;
        $runs[] = $update;
        $this->writeJson($this->basePath . '/runs.json', $runs);
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
            throw new \RuntimeException('Failed to encode automation data.');
        }

        if (file_put_contents($path, $encoded) === false) {
            throw new \RuntimeException('Failed to write automation data.');
        }
    }
}
