<?php

declare(strict_types=1);

namespace Zcc\Core\Theme;

final class ThemeManager
{
    public const COOKIE_NAME = 'zcc_theme';

    public function bootstrap(): void
    {
        if (!isset($_COOKIE[self::COOKIE_NAME])) {
            return;
        }

        $theme = $_COOKIE[self::COOKIE_NAME];
        if (!in_array($theme, ['light', 'dark'], true)) {
            setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
        }
    }

    public function current(): string
    {
        $theme = $_COOKIE[self::COOKIE_NAME] ?? 'dark';
        return in_array($theme, ['light', 'dark'], true) ? $theme : 'dark';
    }
}
