<?php

declare(strict_types=1);

namespace Zcc\Core\Permissions;

final class PermissionGate
{
    public function allows(?array $user, string $capability): bool
    {
        if (!$user) {
            return false;
        }

        if (($user['role'] ?? '') === 'admin') {
            return true;
        }

        $permissions = $user['permissions'] ?? [];
        return in_array($capability, $permissions, true);
    }
}
