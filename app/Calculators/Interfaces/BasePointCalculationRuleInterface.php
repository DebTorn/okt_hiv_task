<?php

namespace App\Calculators\Interfaces;

use App\Models\Applicant;

interface BasePointCalculationRuleInterface
{
    /**
     * @param Applicant $applicant
     * @return bool
     */
    public function supports(
        Applicant $applicant
    ): bool;

    /**
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(
        Applicant $applicant
    ): int;
}