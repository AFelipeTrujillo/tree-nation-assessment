<?php

declare(strict_types=1);

namespace App\TreePlanting\Application;

use App\TreePlanting\Domain\CustomerStats;
use App\TreePlanting\Domain\CustomerStatsRepository;

final readonly class GetCustomerStats
{
    public function __construct(private CustomerStatsRepository $customers)
    {
    }

    public function __invoke(string $customerId): ?CustomerStats
    {
        return $this->customers->get($customerId);
    }
}
