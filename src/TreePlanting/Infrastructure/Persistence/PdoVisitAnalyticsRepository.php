<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Persistence;

use App\TreePlanting\Domain\VisitAnalyticsRepository;
use PDO;

final readonly class PdoVisitAnalyticsRepository implements VisitAnalyticsRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function visitsPerHour(): array
    {
        $statement = $this->pdo->query(
            "SELECT strftime('%H:00', occurred_at) AS hour,
                    COUNT(*) AS visits
             FROM visits
             GROUP BY hour
             ORDER BY hour ASC"
        );

        $rows = $statement->fetchAll();

        return array_map(
            static fn (array $row): array => [
                'hour' => (string) $row['hour'],
                'visits' => (int) $row['visits'],
            ],
            $rows,
        );
    }
}
