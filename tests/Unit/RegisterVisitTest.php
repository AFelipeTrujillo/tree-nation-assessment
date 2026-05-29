<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\TreePlanting\Application\RegisterVisit;
use App\TreePlanting\Domain\CustomerStats;
use App\TreePlanting\Domain\CustomerStatsRepository;
use App\TreePlanting\Domain\TreePlantingPolicy;
use App\TreePlanting\Domain\VisitRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class RegisterVisitTest extends TestCase
{
    public function testItRegistersVisitAndUpdatesCustomerStats(): void
    {
        $visits = new InMemoryVisitRepository();
        $customers = new InMemoryCustomerStatsRepository();
        $useCase = new RegisterVisit($visits, $customers, new TreePlantingPolicy(5));

        $stats = $useCase(
            customerId: 'cus_123',
            shopId: 'shop_001',
            occurredAt: new DateTimeImmutable('2026-05-29T10:15:00Z'),
            eventId: 'evt_001',
        );

        self::assertSame('cus_123', $stats->customerId);
        self::assertSame(1, $stats->totalVisits);
        self::assertSame(0, $stats->treesPlanted);
        self::assertSame('2026-05-29T10:15:00+00:00', $stats->lastConnectionAt?->format(DATE_ATOM));
    }

    public function testItDoesNotCountDuplicateEventTwice(): void
    {
        $visits = new InMemoryVisitRepository();
        $customers = new InMemoryCustomerStatsRepository();
        $useCase = new RegisterVisit($visits, $customers, new TreePlantingPolicy(5));

        $useCase('cus_123', 'shop_001', new DateTimeImmutable('2026-05-29T10:15:00Z'), 'evt_001');
        $stats = $useCase('cus_123', 'shop_001', new DateTimeImmutable('2026-05-29T10:16:00Z'), 'evt_001');

        self::assertSame(1, $stats->totalVisits);
    }
}

final class InMemoryVisitRepository implements VisitRepository
{
    /** @var array<string, bool> */
    private array $eventIds = [];

    public function existsByEventId(string $eventId): bool
    {
        return isset($this->eventIds[$eventId]);
    }

    public function save(string $customerId, string $shopId, DateTimeImmutable $occurredAt, ?string $eventId): void
    {
        if ($eventId !== null) {
            $this->eventIds[$eventId] = true;
        }
    }
}

final class InMemoryCustomerStatsRepository implements CustomerStatsRepository
{
    /** @var array<string, CustomerStats> */
    private array $items = [];

    public function get(string $customerId): ?CustomerStats
    {
        return $this->items[$customerId] ?? null;
    }

    public function getOrCreate(string $customerId): CustomerStats
    {
        return $this->items[$customerId] ?? CustomerStats::create($customerId, new DateTimeImmutable('2026-05-29T10:00:00Z'));
    }

    public function save(CustomerStats $stats): void
    {
        $this->items[$stats->customerId] = $stats;
    }
}
