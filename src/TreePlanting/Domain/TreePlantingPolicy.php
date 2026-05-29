<?php

declare(strict_types=1);

namespace App\TreePlanting\Domain;

use InvalidArgumentException;

final readonly class TreePlantingPolicy
{
    public function __construct(private int $visitsPerTree)
    {
        if ($visitsPerTree <= 0) {
            throw new InvalidArgumentException('Visits per tree must be greater than zero.');
        }
    }

    public function treesForVisits(int $totalVisits): int
    {
        return intdiv($totalVisits, $this->visitsPerTree);
    }

    public function visitsUntilNextTree(int $totalVisits): int
    {
        $remaining = $totalVisits % $this->visitsPerTree;

        return $remaining === 0 ? 0 : $this->visitsPerTree - $remaining;
    }
}
