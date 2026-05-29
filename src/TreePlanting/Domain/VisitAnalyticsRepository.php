<?php

declare(strict_types=1);

namespace App\TreePlanting\Domain;

interface VisitAnalyticsRepository
{
    /**
     * @return list<array{hour: string, visits: int}>
     */
    public function visitsPerHour(): array;
}
