<?php

declare(strict_types=1);

namespace Zcc\Core\Modules;

final class ModuleRegistry
{
    public function __construct(private readonly string $storagePath)
    {
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
