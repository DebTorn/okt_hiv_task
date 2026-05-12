<?php

namespace App\Tests\Calculators\Rules;

use App\Enums\ExamType;
use App\Models\Applicant;
use App\Models\ExamResult;
use App\Providers\Config;
use App\Calculators\Rules\AdvancedExamCalculationRule;
use PHPUnit\Framework\TestCase;

class AdvancedExamCalculationRuleTest extends TestCase
{

    public static function calculationProvider(): array
    {
        return [

            'no exams returns zero' => [
                'examTypes' => [],
                'bonusPerExam' => 50,
                'expected' => 0,
            ],

            'single advanced exam' => [
                'examTypes' => [
                    ExamType::ADVANCED,
                ],
                'bonusPerExam' => 50,
                'expected' => 50,
            ],

            'multiple advanced exams' => [
                'examTypes' => [
                    ExamType::ADVANCED,
                    ExamType::ADVANCED,
                    ExamType::ADVANCED,
                ],
                'bonusPerExam' => 50,
                'expected' => 150,
            ],

            'mixed exam types' => [
                'examTypes' => [
                    ExamType::ADVANCED,
                    ExamType::MID,
                    ExamType::ADVANCED,
                ],
                'bonusPerExam' => 50,
                'expected' => 100,
            ],

            'only mid exams' => [
                'examTypes' => [
                    ExamType::MID,
                    ExamType::MID,
                ],
                'bonusPerExam' => 50,
                'expected' => 0,
            ],
        ];
    }

    /**
     * @dataProvider calculationProvider
     */
    public function testCalculation(
        array $examTypes,
        int $bonusPerExam,
        int $expected
    ): void {
        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('extra_points.advanced_exam')
            ->willReturn($bonusPerExam);

        $examResults = [];

        foreach ($examTypes as $type) {

            $examResult = $this->createMock(ExamResult::class);

            $examResult->method('getType')
                ->willReturn($type);

            $examResults[] = $examResult;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($examResults);

        $rule = new AdvancedExamCalculationRule($config);

        $result = $rule->calculate($applicant);

        $this->assertSame($expected, $result);
    }
}
