<?php

declare(strict_types=1);

namespace Zcc\Core\Backup;

use Zcc\Core\Settings\SettingsRepository;

final class BackupManager
{
    public function __construct(
        private readonly string $localPath,
        private readonly SettingsRepository $nextcloudSettings,
    ) {
    }

    public function createDatabaseDump(): string
    {
        if (!is_dir($this->localPath) && !mkdir($this->localPath, 0755, true)) {
            throw new \RuntimeException('Backup-Verzeichnis konnte nicht erstellt werden.');
        }

        $file = $this->localPath . '/db-' . date('Y-m-d_His') . '.sql.gz';
        $payload = "-- ZCC Backup placeholder\n-- Generated at " . date(DATE_ATOM) . "\n";
        $compressed = gzencode($payload);
        if ($compressed === false) {
            throw new \RuntimeException('Konnte Backup nicht komprimieren.');
        }

        if (file_put_contents($file, $compressed) === false) {
            throw new \RuntimeException('Konnte Backup nicht schreiben.');
        }

        return $file;
    }

    public function uploadToNextcloud(string $file): void
    {
        $baseUrl = rtrim((string) $this->nextcloudSettings->get('base_url', ''), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException('Nextcloud base_url fehlt.');
        }

        $root = (string) $this->nextcloudSettings->get('root', '/ZenityDent');
        $targetDir = rtrim($root, '/') . '/backups/db/' . date('Y-m-d');
        $this->ensureWebDavPath($baseUrl, $targetDir);

        $target = $baseUrl . $targetDir . '/' . basename($file);
        $contents = file_get_contents($file);
        if ($contents === false) {
            throw new \RuntimeException('Backup-Datei nicht lesbar.');
        }

        $this->request('PUT', $target, $contents);
    }

    public function rotate(int $keep = 7): void
    {
        $files = glob($this->localPath . '/*.sql.gz') ?: [];
        rsort($files);
        $toDelete = array_slice($files, $keep);
        foreach ($toDelete as $file) {
            @unlink($file);
        }
    }

    private function ensureWebDavPath(string $baseUrl, string $path): void
    {
        $segments = array_filter(explode('/', trim($path, '/')));
        $current = '';
        foreach ($segments as $segment) {
            $current .= '/' . $segment;
            $this->request('MKCOL', $baseUrl . $current, null, [201, 405]);
        }
    }

    private function request(string $method, string $url, ?string $body = null, array $allowed = [200, 201, 204]): void
    {
        $username = (string) $this->nextcloudSettings->get('username', '');
        $password = (string) $this->nextcloudSettings->get('password', '');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream']);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!in_array($status, $allowed, true)) {
            throw new \RuntimeException('WebDAV Fehler (' . $status . ').');
        }
    }
}
