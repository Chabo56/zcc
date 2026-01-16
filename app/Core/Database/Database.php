<?php

declare(strict_types=1);

namespace Zcc\Core\Database;

final class Database
{
    public function __construct(private readonly string $configPath)
    {
    }

    public function connect(): \PDO
    {
        if (!is_file($this->configPath)) {
            throw new \RuntimeException('DB-Konfiguration fehlt.');
        }

        $raw = file_get_contents($this->configPath);
        if ($raw === false) {
            throw new \RuntimeException('DB-Konfiguration nicht lesbar.');
        }

        $config = json_decode($raw, true);
        if (!is_array($config)) {
            throw new \RuntimeException('DB-Konfiguration ungültig.');
        }

        $driver = $config['driver'] ?? 'mysql';
        if ($driver !== 'mysql') {
            throw new \RuntimeException('Nur MySQL wird im MVP unterstützt.');
        }

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $config['host'] ?? '',
            $config['database'] ?? ''
        );

        return new \PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public function applySchema(string $schemaPath): void
    {
        $pdo = $this->connect();
        if (!is_file($schemaPath)) {
            throw new \RuntimeException('Schema-Datei fehlt.');
        }

        $schema = file_get_contents($schemaPath);
        if ($schema === false) {
            throw new \RuntimeException('Schema-Datei nicht lesbar.');
        }

        $pdo->exec($schema);
    }
}
