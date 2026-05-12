<?php

namespace App\Calculators;

use App\Calculators\Interfaces\ExtraPointCalculationRuleInterface;
use App\Models\ExtraPoint;
use App\Providers\Config;

class ExtraPointCalculator
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @var ExtraPointCalculationRuleInterface[] $rules
     */
    private array $rules;

    /**
     * @param Config $_config
     * @param ExtraPointCalculationRuleInterface[] $_rules
     */
    public function __construct(
        Config $_config,
        array $_rules
    )
    {
        $this->config = $_config;
        $this->rules = $_rules;
    }

    /**
     * Kalkulációs szabályok szerint többletpontok összesítése
     *
     * @param ExtraPoint[] $points
     * @return int
     */
    public function calculate(array $points): int
    {
        $sum = 0;
        foreach ($this->rules as $rule) {

            //Szabálynak megfelelő pontok legyűjtése
            $supportedPoints = [];
            foreach ($points as $point) {
                if ($rule->supports($point)) {
                    $supportedPoints[] = $point;
                }
            }

            //Ha nincsenek a szabálynak megfelelő pontok, továbblépünk a követező szabályra
            if (empty($supportedPoints)) {
                continue;
            }

            //Hozzáadjuk a szabályból kiszámolt összeget a végeredményhez
            $sum += $rule->calculate($supportedPoints);
        }

        return $sum;
    }

}