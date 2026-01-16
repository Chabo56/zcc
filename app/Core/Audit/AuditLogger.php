<?php

declare(strict_types=1);

namespace Zcc\Core\Audit;

final class AuditLogger
{
    public function __construct(private readonly string $path)
    {
    }

    public function log(string $action, array $context = []): void
    {
        $payload = [
            'time' => date(DATE_ATOM),
            'action' => $action,
            'context' => $context,
        ];

        $line = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($line === false) {
            return;
        }

        file_put_contents($this->path, $line . PHP_EOL, FILE_APPEND);
    }

    public function recent(int $limit = 50): array
    {
        if (!is_file($this->path)) {
            return [];
        }

        $lines = file($this->path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $lines = array_slice(array_reverse($lines), 0, $limit);
        $entries = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            if (is_array($decoded)) {
                $entries[] = $decoded;
            }
        }

        return $entries;
    }
}
