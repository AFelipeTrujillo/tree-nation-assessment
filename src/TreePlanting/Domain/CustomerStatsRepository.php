<?php

declare(strict_types=1);

namespace App\TreePlanting\Domain;

interface CustomerStatsRepository
{
    public function get(string $customerId): ?CustomerStats;

    public function getOrCreate(string $customerId): CustomerStats;

    public function save(CustomerStats $stats): void;
}
