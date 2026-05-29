<?php

declare(strict_types=1);

namespace App\TreePlanting\Domain;

use DateTimeImmutable;

final readonly class CustomerStats
{
    public function __construct(
        public string $customerId,
        public int $totalVisits,
        public int $treesPlanted,
        public ?DateTimeImmutable $lastConnectionAt,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
    ) {
    }

    public static function create(string $customerId, DateTimeImmutable $now): self
    {
        return new self(
            customerId: $customerId,
            totalVisits: 0,
            treesPlanted: 0,
            lastConnectionAt: null,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public function recordVisit(DateTimeImmutable $occurredAt, int $treesPlanted): self
    {
        $now = new DateTimeImmutable('now');

        return new self(
            customerId: $this->customerId,
            totalVisits: $this->totalVisits + 1,
            treesPlanted: $treesPlanted,
            lastConnectionAt: $occurredAt,
            createdAt: $this->createdAt,
            updatedAt: $now,
        );
    }
}
