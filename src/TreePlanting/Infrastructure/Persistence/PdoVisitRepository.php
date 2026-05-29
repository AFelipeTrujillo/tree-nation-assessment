<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Persistence;

use App\TreePlanting\Domain\VisitRepository;
use DateTimeImmutable;
use PDO;

final readonly class PdoVisitRepository implements VisitRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function existsByEventId(string $eventId): bool
    {
        $statement = $this->pdo->prepare('SELECT 1 FROM visits WHERE event_id = :event_id LIMIT 1');
        $statement->execute(['event_id' => $eventId]);

        return $statement->fetchColumn() !== false;
    }

    public function save(
        string $customerId,
        string $shopId,
        DateTimeImmutable $occurredAt,
        ?string $eventId,
    ): void {
        $statement = $this->pdo->prepare(
            'INSERT INTO visits (event_id, customer_id, shop_id, occurred_at, received_at)
             VALUES (:event_id, :customer_id, :shop_id, :occurred_at, :received_at)'
        );

        $statement->execute([
            'event_id' => $eventId,
            'customer_id' => $customerId,
            'shop_id' => $shopId,
            'occurred_at' => $occurredAt->format(DATE_ATOM),
            'received_at' => (new DateTimeImmutable('now'))->format(DATE_ATOM),
        ]);
    }
}
