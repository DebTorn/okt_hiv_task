<?php

namespace App\Calculators\Rules;

use App\Calculators\Interfaces\ExamBonusCalculationRuleInterface;
use App\Enums\ExamType;
use App\Models\Applicant;
use App\Providers\Config;

class AdvancedExamCalculationRule implements ExamBonusCalculationRuleInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $_config
     */
    public function __construct(
        Config $_config
    ) {
        $this->config = $_config;
    }

    /**
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(Applicant $applicant): int
    {
        //Emelt érettségi pluszpont lekérdezése configból
        $bonusPerExam = $this->config->get(
            'extra_points.advanced_exam'
        );

        // Emelt érettségik pontjainak összesítése advanced_exam alapján
        $sum = 0;
        foreach ($applicant->getExamResults() as $result) {
            if ($result->getType() === ExamType::ADVANCED) {
                $sum += $bonusPerExam;
            }
        }

        return $sum;
    }
}