<?php

namespace App\Calculators\Interfaces;

use App\Models\ExtraPoint;

interface ExtraPointCalculationRuleInterface
{
    /**
     * @param ExtraPoint $point
     * @return bool
     */
    public function supports(ExtraPoint $point): bool;

    /**
     * @param ExtraPoint[] $points
     * @return int
     */
    public function calculate(array $points): int;
}