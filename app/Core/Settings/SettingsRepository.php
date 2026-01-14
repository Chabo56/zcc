<?php

declare(strict_types=1);

namespace Zcc\Core\Settings;

final class SettingsRepository
{
    public function __construct(private readonly string $path)
    {
    }

    public function all(): array
    {
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
        $settings = $this->all();
        $settings[$key] = $value;
        $this->save($settings);
    }

    public function save(array $settings): void
    {
        $payload = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException('Failed to encode settings.');
        }

        if (file_put_contents($this->path, $payload) === false) {
            throw new \RuntimeException('Failed to write settings.');
        }
    }
}
