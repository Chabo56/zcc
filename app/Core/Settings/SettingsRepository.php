<?php

declare(strict_types=1);

namespace Zcc\Core\Settings;

final class SettingsRepository
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly string $path, ?\Zcc\Core\Database\DbConnector $connector = null)
    {
        if ($connector && $connector->isConfigured()) {
            try {
                $this->pdo = $connector->connect();
            } catch (\Throwable) {
                $this->pdo = null;
            }
        }
    }

    public function all(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT setting_key, setting_value FROM settings');
            $rows = $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = json_decode($row['setting_value'], true);
            }
            return $settings;
        }

        if (!is_file($this->path)) {
            return [];
        }

        $raw = file_get_contents($this->path);
        if ($raw === false) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();
        return $settings[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->pdo) {
            $payload = json_encode($value, JSON_UNESCAPED_SLASHES);
            $stmt = $this->pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
            $stmt->execute(['key' => $key, 'value' => $payload]);
            return;
        }

        $settings = $this->all();
        $settings[$key] = $value;
        $this->save($settings);
    }

    public function save(array $settings): void
    {
        if ($this->pdo) {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
            return;
        }

        $payload = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException('Failed to encode settings.');
        }

        if (file_put_contents($this->path, $payload) === false) {
            throw new \RuntimeException('Failed to write settings.');
        }
    }
}
