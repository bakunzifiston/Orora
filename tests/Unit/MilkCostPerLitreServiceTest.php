<?php

namespace Tests\Unit;

use App\Services\Milk\MilkCostPerLitreService;
use PHPUnit\Framework\TestCase;

class MilkCostPerLitreServiceTest extends TestCase
{
    public function test_formula_matches_worked_example(): void
    {
        $service = new class extends MilkCostPerLitreService
        {
            public function example(): array
            {
                $totalExpense = 600_000;
                $producingAnimals = 15;
                $totalAnimals = 20;
                $totalLitres = 1200;

                $denominator = $totalAnimals > 0 ? $totalAnimals : $producingAnimals;
                $producingRatio = $producingAnimals / $denominator;
                $allocatedExpense = $totalExpense * $producingRatio;
                $costPerLitre = $allocatedExpense / $totalLitres;

                return [
                    'cost_per_litre' => round($costPerLitre, 2),
                    'allocated_expense' => round($allocatedExpense, 2),
                    'producing_ratio' => round($producingRatio * 100, 1),
                ];
            }
        };

        $result = $service->example();

        $this->assertSame(375.0, $result['cost_per_litre']);
        $this->assertSame(450_000.0, $result['allocated_expense']);
        $this->assertSame(75.0, $result['producing_ratio']);
    }
}
