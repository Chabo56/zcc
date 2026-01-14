<?php

declare(strict_types=1);

namespace Zcc\Core\Automation;

final class AutomationStorage
{
    public function __construct(private readonly string $basePath)
    {
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
        return $this->readJson($this->basePath . '/runs.json', []);
    }

    public function appendRun(array $run): void
    {
        $runs = $this->runs();
        $runs[] = $run;
        $this->writeJson($this->basePath . '/runs.json', $runs);
    }

    public function updateRun(string $requestId, array $update): void
    {
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
