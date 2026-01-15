<?php

declare(strict_types=1);

namespace Zcc\Core\Mail;

final class MailStorage
{
    public function __construct(private readonly string $basePath)
    {
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
        return $this->readJson($this->basePath . '/index.json', []);
    }

    public function drafts(): array
    {
        return $this->readJson($this->basePath . '/drafts.json', []);
    }

    public function saveDraft(array $draft): void
    {
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
        return $this->readJson($this->basePath . '/requests.json', []);
    }

    public function appendRequest(array $request): void
    {
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
