<?php

declare(strict_types=1);

namespace Zcc\Core\Auth;

final class AuthManager
{
    private const SESSION_KEY = 'zcc_user';

    public function __construct(
        private readonly array $config,
        private readonly ?\Zcc\Core\Settings\SettingsRepository $settings = null,
    )
    {
    }

    public function attempt(string $username, string $password): bool
    {
        $admin = $this->config['default_admin'] ?? [];
        if ($this->settings) {
            $stored = $this->settings->get('admin', []);
            if (is_array($stored) && $stored !== []) {
                $admin = $stored;
            }
        }
        if ($username !== ($admin['username'] ?? '') || $password !== ($admin['password'] ?? '')) {
            return false;
        }

        $_SESSION[self::SESSION_KEY] = [
            'username' => $username,
            'role' => 'admin',
        ];

        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
