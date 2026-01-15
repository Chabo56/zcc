<?php

declare(strict_types=1);

namespace Zcc\Core\Shopware;

final class ShopwareStorage
{
    private ?\PDO $pdo = null;

    public function __construct(private readonly string $basePath, ?\Zcc\Core\Database\DbConnector $connector = null)
    {
        if ($connector && $connector->isConfigured()) {
            try {
                $this->pdo = $connector->connect();
            } catch (\Throwable) {
                $this->pdo = null;
            }
        }
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
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT orders_today, revenue_today, last_updated FROM shopware_metrics ORDER BY id DESC LIMIT 1');
            $row = $stmt ? $stmt->fetch(\PDO::FETCH_ASSOC) : false;
            return $row ?: ['orders_today' => 0, 'revenue_today' => 0, 'last_updated' => null];
        }

        return $this->readJson($this->basePath . '/metrics.json', [
            'orders_today' => 0,
            'revenue_today' => 0,
            'last_updated' => null,
        ]);
    }

    public function saveMetrics(array $metrics): void
    {
        if ($this->pdo) {
            $stmt = $this->pdo->prepare('INSERT INTO shopware_metrics (orders_today, revenue_today, last_updated) VALUES (:orders_today, :revenue_today, :last_updated)');
            $stmt->execute([
                'orders_today' => $metrics['orders_today'] ?? 0,
                'revenue_today' => $metrics['revenue_today'] ?? 0,
                'last_updated' => $metrics['last_updated'] ?? null,
            ]);
            return;
        }

        $this->writeJson($this->basePath . '/metrics.json', $metrics);
    }

    public function orders(): array
    {
        if ($this->pdo) {
            $stmt = $this->pdo->query('SELECT order_number AS number, customer, amount, ordered_at AS time, status FROM shopware_orders ORDER BY ordered_at DESC LIMIT 5');
            return $stmt ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
        }

        return $this->readJson($this->basePath . '/orders.json', []);
    }

    public function saveOrders(array $orders): void
    {
        if ($this->pdo) {
            foreach ($orders as $order) {
                $stmt = $this->pdo->prepare('INSERT INTO shopware_orders (order_number, customer, amount, status, ordered_at) VALUES (:order_number, :customer, :amount, :status, :ordered_at)');
                $stmt->execute([
                    'order_number' => $order['number'] ?? '',
                    'customer' => $order['customer'] ?? '',
                    'amount' => $order['amount'] ?? 0,
                    'status' => $order['status'] ?? '',
                    'ordered_at' => $order['time'] ?? null,
                ]);
            }
            return;
        }

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
