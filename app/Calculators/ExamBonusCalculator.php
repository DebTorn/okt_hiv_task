<?php

namespace App\Calculators;

use App\Calculators\Interfaces\ExamBonusCalculationRuleInterface;
use App\Models\Applicant;

class ExamBonusCalculator
{
    /**
     * @var ExamBonusCalculationRuleInterface[]
     */
    private array $rules;

    /**
     * @param array $rules
     */
    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Emelt érettségikből pluszpontok számítása számítási szabályok alapján
     *
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(Applicant $applicant): int
    {
        $total = 0;
        foreach ($this->rules as $rule) {
            $total += $rule->calculate($applicant);
        }

        return $total;
    }
}