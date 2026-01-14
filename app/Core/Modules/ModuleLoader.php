<?php

declare(strict_types=1);

namespace Zcc\Core\Modules;

final class ModuleLoader
{
    public function __construct(
        private readonly string $moduleBasePath,
        private readonly ModuleRegistry $registry,
    ) {
    }

    public function resolve(string $key): ?string
    {
        $module = $this->registry->find($key);
        if (!$module || !($module['enabled'] ?? false)) {
            return null;
        }

        $path = $this->moduleBasePath . '/' . $key . '/index.php';
        return is_file($path) ? $path : null;
    }
}
