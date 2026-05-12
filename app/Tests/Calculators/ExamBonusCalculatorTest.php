<?php

namespace Tests\Unit\Calculators;

use App\Calculators\ExamBonusCalculator;
use App\Calculators\Rules\AdvancedExamCalculationRule;
use App\Enums\ExamType;
use App\Models\Applicant;
use App\Models\ExamResult;
use App\Providers\Config;
use PHPUnit\Framework\TestCase;

class ExamBonusCalculatorTest extends TestCase
{

    public static function calculationProvider(): array
    {
        return [
            'no exams' => [
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
                ],
                'bonusPerExam' => 50,
                'expected' => 100,
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

            'only standard exams' => [
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
    public function testCalculation(array $examTypes, int $bonusPerExam, int $expected): void
    {
        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('extra_points.advanced_exam')
            ->willReturn($bonusPerExam);

        $rule = new AdvancedExamCalculationRule($config);

        $calculator = new ExamBonusCalculator([
            $rule,
        ]);

        $examResults = [];

        foreach ($examTypes as $type) {

            $exam = $this->createMock(ExamResult::class);

            $exam->method('getType')
                ->willReturn($type);

            $examResults[] = $exam;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($examResults);

        $result = $calculator->calculate($applicant);

        $this->assertSame($expected, $result);
    }
}