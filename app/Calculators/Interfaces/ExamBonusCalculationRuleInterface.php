<?php

namespace App\Calculators\Interfaces;

use App\Models\Applicant;

interface ExamBonusCalculationRuleInterface
{
    /**
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(Applicant $applicant): int;
}