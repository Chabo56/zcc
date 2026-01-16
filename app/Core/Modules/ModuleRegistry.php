<?php

declare(strict_types=1);

namespace Zcc\Core\Modules;

final class ModuleRegistry
{
    public function __construct(private readonly string $storagePath)
    {
    }

    public function save(array $modules): void
    {
        $payload = json_encode(array_values($modules), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            throw new \RuntimeException('Failed to encode module registry.');
        }

        if (file_put_contents($this->storagePath, $payload) === false) {
            throw new \RuntimeException('Failed to write module registry.');
        }
    }

    public function all(): array
    {
        if (!is_file($this->storagePath)) {
            return [];
        }

        $raw = file_get_contents($this->storagePath);
        if ($raw === false) {
            return [];
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    public function register(array $module): void
    {
        $modules = $this->all();
        $key = $module['key'] ?? null;
        if (!$key) {
            throw new \InvalidArgumentException('Module key missing.');
        }

        $updated = false;
        foreach ($modules as $index => $entry) {
            if (($entry['key'] ?? '') === $key) {
                $modules[$index] = array_merge($entry, $module);
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $modules[] = $module;
        }

        $this->save($modules);
    }

    public function setEnabled(string $key, bool $enabled): void
    {
        $modules = $this->all();
        foreach ($modules as $index => $entry) {
            if (($entry['key'] ?? '') === $key) {
                $modules[$index]['enabled'] = $enabled;
                $this->save($modules);
                return;
            }
        }
    }

    public function find(string $key): ?array
    {
        foreach ($this->all() as $module) {
            if (($module['key'] ?? '') === $key) {
                return $module;
            }
        }

        return null;
    }
}
