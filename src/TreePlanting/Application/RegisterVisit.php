<?php

declare(strict_types=1);

namespace App\TreePlanting\Application;

use App\TreePlanting\Domain\CustomerStats;
use App\TreePlanting\Domain\CustomerStatsRepository;
use App\TreePlanting\Domain\TreePlantingPolicy;
use App\TreePlanting\Domain\VisitRepository;
use DateTimeImmutable;
use RuntimeException;

final readonly class RegisterVisit
{
    public function __construct(
        private VisitRepository $visits,
        private CustomerStatsRepository $customers,
        private TreePlantingPolicy $policy,
    ) {
    }

    public function __invoke(
        string $customerId,
        string $shopId,
        DateTimeImmutable $occurredAt,
        ?string $eventId = null,
    ): CustomerStats {
        if ($eventId !== null && $this->visits->existsByEventId($eventId)) {
            $existingStats = $this->customers->get($customerId);

            if ($existingStats === null) {
                throw new RuntimeException('Duplicate event found but customer stats do not exist.');
            }

            return $existingStats;
        }

        $this->visits->save($customerId, $shopId, $occurredAt, $eventId);

        $stats = $this->customers->getOrCreate($customerId);
        $newTotalVisits = $stats->totalVisits + 1;
        $treesPlanted = $this->policy->treesForVisits($newTotalVisits);
        $updatedStats = $stats->recordVisit($occurredAt, $treesPlanted);

        $this->customers->save($updatedStats);

        return $updatedStats;
    }
}
