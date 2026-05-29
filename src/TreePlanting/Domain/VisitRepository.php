<?php

declare(strict_types=1);

namespace App\TreePlanting\Domain;

use DateTimeImmutable;

interface VisitRepository
{
    public function existsByEventId(string $eventId): bool;

    public function save(
        string $customerId,
        string $shopId,
        DateTimeImmutable $occurredAt,
        ?string $eventId,
    ): void;
}
