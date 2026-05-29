<?php

declare(strict_types=1);

namespace App\TreePlanting\Infrastructure\Http;

use App\TreePlanting\Domain\CustomerStats;
use App\TreePlanting\Domain\TreePlantingPolicy;

final readonly class CustomerStatsPresenter
{
    public function __construct(private TreePlantingPolicy $policy)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function present(CustomerStats $stats): array
    {
        return [
            'customerId' => $stats->customerId,
            'totalVisits' => $stats->totalVisits,
            'treesPlanted' => $stats->treesPlanted,
            'lastConnectionAt' => $stats->lastConnectionAt?->format(DATE_ATOM),
            'visitsUntilNextTree' => $this->policy->visitsUntilNextTree($stats->totalVisits),
        ];
    }
}
