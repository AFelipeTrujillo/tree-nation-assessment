<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\TreePlanting\Domain\TreePlantingPolicy;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class TreePlantingPolicyTest extends TestCase
{
    public function testItCalculatesTreesForVisits(): void
    {
        $policy = new TreePlantingPolicy(5);

        self::assertSame(0, $policy->treesForVisits(4));
        self::assertSame(1, $policy->treesForVisits(5));
        self::assertSame(2, $policy->treesForVisits(10));
    }

    public function testItCalculatesVisitsUntilNextTree(): void
    {
        $policy = new TreePlantingPolicy(5);

        self::assertSame(1, $policy->visitsUntilNextTree(4));
        self::assertSame(0, $policy->visitsUntilNextTree(5));
        self::assertSame(4, $policy->visitsUntilNextTree(6));
    }

    public function testVisitsPerTreeMustBeGreaterThanZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TreePlantingPolicy(0);
    }
}
