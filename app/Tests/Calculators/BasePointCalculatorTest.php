<?php

namespace App\Tests\Calculators;

use App\Calculators\BasePointCalculator;
use App\Enums\ExamType;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\ExamResult;
use App\Providers\Config;
use PHPUnit\Framework\TestCase;

class BasePointCalculatorTest extends TestCase
{
    public static function calculationProvider(): array
    {
        return [
            'required subjects only' => [
                'rules' => [
                    'required' => [
                        'informatika' => ExamType::MID,
                        'matematika' => ExamType::ADVANCED
                    ],
                    'optional' => [],
                ],
                'examResults' => [
                    [
                        'name' => 'magyar nyelv és irodalom',
                        'type' => ExamType::MID,
                        'value' => 30,
                    ],
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 90,
                    ],
                    [
                        'name' => 'angol nyelv',
                        'type' => ExamType::MID,
                        'value' => 94,
                    ],
                    [
                        'name' => 'informatika',
                        'type' => ExamType::MID,
                        'value' => 95,
                    ],
                ],
                'expected' => 370,
            ],

            'required plus best optional' => [
                'rules' => [
                    'required' => [
                        'informatika' => ExamType::ADVANCED
                    ],
                    'optional' => [
                        'angol nyelv' => ExamType::MID,
                        'matematika' => ExamType::MID,
                    ],
                ],
                'examResults' => [
                    [
                        'name' => 'magyar nyelv és irodalom',
                        'type' => ExamType::MID,
                        'value' => 30,
                    ],
                    [
                        'name' => 'matematika',
                        'type' => ExamType::MID,
                        'value' => 90,
                    ],
                    [
                        'name' => 'angol nyelv',
                        'type' => ExamType::MID,
                        'value' => 94,
                    ],
                    [
                        'name' => 'informatika',
                        'type' => ExamType::ADVANCED,
                        'value' => 95,
                    ],
                ],
                'expected' => 378,
            ],

            'irrelevant subjects ignored' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::MID
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID
                    ],
                ],
                'examResults' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::MID,
                        'value' => 80,
                    ],
                    [
                        'name' => 'angol nyelv',
                        'type' => ExamType::MID,
                        'value' => 100,
                    ],
                ],
                'expected' => 160,
            ],

            'empty results' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::MID
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID
                    ],
                ],
                'examResults' => [],
                'expected' => 0,
            ],

            'empty everything' => [
                'rules' => [
                    'required' => [],
                    'optional' => [],
                ],
                'examResults' => [],
                'expected' => 0,
            ],

            'wrong formatted rules' => [
                'rules' => [
                    'required' => [
                        'matematika'
                    ],
                    'optional' => [
                        'fizika'
                    ],
                ],
                'examResults' => [],
                'expected' => 0
            ],

            'REAL_TEST_CASE_1' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::MID,
                    ],

                    'optional' => [
                        'informatika' => ExamType::MID,
                        'fizika' => ExamType::MID,
                        'angol nyelv' => ExamType::MID,
                    ],
                ],
                'examResults' => [
                    [
                        'name' => 'magyar nyelv és irodalom',
                        'type' => ExamType::MID,
                        'value' => 70,
                    ],
                    [
                        'name' => 'történelem',
                        'type' => ExamType::MID,
                        'value' => 80,
                    ],
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 90,
                    ],
                    [
                        'name' => 'angol nyelv',
                        'type' => ExamType::MID,
                        'value' => 94,
                    ],
                    [
                        'name' => 'informatika',
                        'type' => ExamType::ADVANCED,
                        'value' => 95,
                    ],
                ],
                'expected' => 370
            ],
            'REAL_TEST_CASE_2' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::MID,
                    ],

                    'optional' => [
                        'informatika' => ExamType::MID,
                        'fizika' => ExamType::MID,
                        'angol nyelv' => ExamType::MID,
                    ],
                ],
                'examResults' => [
                    [
                        'name' => 'magyar nyelv és irodalom',
                        'type' => ExamType::MID,
                        'value' => 70,
                    ],
                    [
                        'name' => 'történelem',
                        'type' => ExamType::MID,
                        'value' => 80,
                    ],
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 90,
                    ],
                    [
                        'name' => 'angol nyelv',
                        'type' => ExamType::MID,
                        'value' => 94,
                    ],
                    [
                        'name' => 'informatika',
                        'type' => ExamType::MID,
                        'value' => 95,
                    ],
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 98,
                    ],
                ],
                'expected' => 376
            ],
        ];
    }

    public static function exceptionProvider(): array
    {
        return [
            'rule not found' => [
                'config' => null,
                'exam_results' => [],
                'expectedExceptionType' => \RuntimeException::class,
                'expectedExceptionMessage' => 'POINT_RULE_NOT_FOUND'
            ],
        ];
    }

    /**
     * @dataProvider calculationProvider
     */
    public function testCalculation(
        array $rules,
        array $examResultsData,
        int $expected
    ): void {

        $course = $this->createMock(Course::class);

        $course->method('getUniversity')
            ->willReturn('ELTE');

        $course->method('getFaculty')
            ->willReturn('IK');

        $course->method('getName')
            ->willReturn('Programtervező informatikus');

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with(
                'base_points.ELTE.IK.Programtervező informatikus'
            )
            ->willReturn($rules);

        $examResults = [];

        foreach ($examResultsData as $data) {

            $examResult = $this->createMock(ExamResult::class);

            $examResult->method('getName')
                ->willReturn($data['name']);

            $examResult->method('getType')
                ->willReturn($data['type']);

            $examResult->method('getValue')
                ->willReturn($data['value']);

            $examResults[] = $examResult;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getCourse')
            ->willReturn($course);

        $applicant->method('getExamResults')
            ->willReturn($examResults);

        $calculator = new BasePointCalculator($config);

        $result = $calculator->calculate($applicant);

        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testExceptions(
        $conf,
        $examResults,
        $expectedExceptionType,
        $expectedExceptionMessage
    ): void
    {
        $course = $this->createMock(Course::class);

        $course->method('getUniversity')
            ->willReturn('ELTE');

        $course->method('getFaculty')
            ->willReturn('IK');

        $course->method('getName')
            ->willReturn('Programtervező informatikus');

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->willReturn($conf);

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getCourse')
            ->willReturn($course);

        if(!empty($examResults)){
            $collected = [];
            foreach($examResults as $examResultItem){
                $examResult = $this->createMock(ExamResult::class);

                $examResult->method('getName')
                    ->willReturn($examResultItem['name']);

                $examResult->method('getType')
                    ->willReturn($examResultItem['type']);

                $examResult->method('getValue')
                    ->willReturn($examResultItem['value']);

                $collected[] = $examResult;
            }

            $applicant->method('getExamResults')
                ->willReturn($collected);
        }

        $calculator = new BasePointCalculator($config);

        $this->expectException($expectedExceptionType);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $calculator->calculate($applicant);
    }
}
