<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Persistence;

use App\TreePlanting\Domain\CustomerStats;
use App\TreePlanting\Domain\CustomerStatsRepository;
use DateTimeImmutable;
use PDO;

final readonly class PdoCustomerStatsRepository implements CustomerStatsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(string $customerId): ?CustomerStats
    {
        $statement = $this->pdo->prepare('SELECT * FROM customer_stats WHERE customer_id = :customer_id');
        $statement->execute(['customer_id' => $customerId]);
        $row = $statement->fetch();

        if ($row === false) {
            return null;
        }

        return $this->mapRow($row);
    }

    public function getOrCreate(string $customerId): CustomerStats
    {
        $existing = $this->get($customerId);

        if ($existing !== null) {
            return $existing;
        }

        return CustomerStats::create($customerId, new DateTimeImmutable('now'));
    }

    public function save(CustomerStats $stats): void
    {
        $sql = <<<SQL
INSERT INTO customer_stats (
    customer_id,
    total_visits,
    trees_planted,
    last_connection_at,
    created_at,
    updated_at
) VALUES (
    :customer_id,
    :total_visits,
    :trees_planted,
    :last_connection_at,
    :created_at,
    :updated_at
)
ON CONFLICT(customer_id) DO UPDATE SET
    total_visits = excluded.total_visits,
    trees_planted = excluded.trees_planted,
    last_connection_at = excluded.last_connection_at,
    updated_at = excluded.updated_at
SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'customer_id' => $stats->customerId,
            'total_visits' => $stats->totalVisits,
            'trees_planted' => $stats->treesPlanted,
            'last_connection_at' => $stats->lastConnectionAt?->format(DATE_ATOM),
            'created_at' => $stats->createdAt->format(DATE_ATOM),
            'updated_at' => $stats->updatedAt->format(DATE_ATOM),
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapRow(array $row): CustomerStats
    {
        return new CustomerStats(
            customerId: (string) $row['customer_id'],
            totalVisits: (int) $row['total_visits'],
            treesPlanted: (int) $row['trees_planted'],
            lastConnectionAt: $row['last_connection_at'] !== null ? new DateTimeImmutable((string) $row['last_connection_at']) : null,
            createdAt: new DateTimeImmutable((string) $row['created_at']),
            updatedAt: new DateTimeImmutable((string) $row['updated_at']),
        );
    }
}
