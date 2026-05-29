<?php

declare(strict_types=1);

namespace App\TreePlanting\Application;

use App\TreePlanting\Domain\VisitAnalyticsRepository;

final readonly class GetVisitsPerHour
{
    public function __construct(private VisitAnalyticsRepository $analytics)
    {
    }

    /**
     * @return list<array{hour: string, visits: int}>
     */
    public function __invoke(): array
    {
        return $this->analytics->visitsPerHour();
    }
}
