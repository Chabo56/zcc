<?php

declare(strict_types=1);

namespace Zcc\Core\Database;

final class DbConnector
{
    public function __construct(private readonly string $configPath)
    {
    }

    public function isConfigured(): bool
    {
        return is_file($this->configPath);
    }

    public function connect(): \PDO
    {
        $database = new Database($this->configPath);
        return $database->connect();
    }
}
