<?php

declare(strict_types=1);

namespace Zcc\Core\Shopware;

final class ShopwareStorage
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function settings(): array
    {
        return $this->readJson($this->basePath . '/settings.json', [
            'n8n_url' => '',
        ]);
    }

    public function saveSettings(array $settings): void
    {
        $this->writeJson($this->basePath . '/settings.json', $settings);
    }

    public function metrics(): array
    {
        return $this->readJson($this->basePath . '/metrics.json', [
            'orders_today' => 0,
            'revenue_today' => 0,
            'last_updated' => null,
        ]);
    }

    public function saveMetrics(array $metrics): void
    {
        $this->writeJson($this->basePath . '/metrics.json', $metrics);
    }

    public function orders(): array
    {
        return $this->readJson($this->basePath . '/orders.json', []);
    }

    public function saveOrders(array $orders): void
    {
        $this->writeJson($this->basePath . '/orders.json', $orders);
    }

    private function readJson(string $path, array $default): array
    {
        if (!is_file($path)) {
            return $default;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return $default;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : $default;
    }

    private function writeJson(string $path, array $payload): void
    {
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            throw new \RuntimeException('Failed to encode shopware data.');
        }

        if (file_put_contents($path, $encoded) === false) {
            throw new \RuntimeException('Failed to write shopware data.');
        }
    }
}
