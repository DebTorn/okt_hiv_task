<?php

namespace Tests\Unit\Validators;

use App\Enums\ExamType;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\ExamResult;
use App\Providers\Config;
use App\Validators\RequiredAndOptionalSubjectByMajorValidator;
use PHPUnit\Framework\TestCase;

class RequiredAndOptionalSubjectByMajorValidatorTest extends TestCase
{
    public static function validProvider(): array
    {
        return [

            'required and optional subjects' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::ADVANCED,
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
                'examResultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 30
                    ],
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 40
                    ],
                ],
            ],

            'multiple required subjects' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::ADVANCED,
                        'történelem' => ExamType::MID,
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
                'examResultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 30
                    ],
                    [
                        'name' => 'történelem',
                        'type' => ExamType::MID,
                        'value' => 40
                    ],
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 50
                    ],
                ],
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
                'examResultsData' => [
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
                ]
            ]
        ];
    }

    public static function invalidProvider(): array
    {
        return [

            'missing required subject' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::ADVANCED,
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
                'examResultsData' => [
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 30
                    ],
                ],
                'expectedErrors' => [
                    'missing_required' => [
                        'matematika',
                    ],
                    'missing_optional' => [],
                ],
            ],

            'missing optional subject' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::ADVANCED,
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
                'examResultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::ADVANCED,
                        'value' => 30
                    ],
                ],
                'expectedErrors' => [
                    'missing_required' => [],
                    'missing_optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
            ],

            'wrong exam type for required subject' => [
                'rules' => [
                    'required' => [
                        'matematika' => ExamType::ADVANCED,
                    ],
                    'optional' => [
                        'fizika' => ExamType::MID,
                    ],
                ],
                'examResultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::MID,
                        'value' => 30
                    ],
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 40
                    ],
                ],
                'expectedErrors' => [
                    'missing_required' => [
                        'matematika',
                    ],
                    'missing_optional' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testValidatePasses(
        array $rules,
        array $examResultsData
    ): void {

        $course = $this->createMock(Course::class);

        $course->method('getUniversity')
            ->willReturn('egyetem');

        $course->method('getFaculty')
            ->willReturn('kar');

        $course->method('getName')
            ->willReturn('szak');

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('base_points.egyetem.kar.szak')
            ->willReturn($rules);

        $results = [];

        foreach ($examResultsData as $data) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($data['name']);

            $result->method('getType')
                ->willReturn($data['type']);

            $result->method('getValue')
                ->willReturn($data['value']);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getCourse')
            ->willReturn($course);

        $applicant->method('getExamResults')
            ->willReturn($results);

        $validator = new RequiredAndOptionalSubjectByMajorValidator($config);

        $validator->validate($applicant);

        $this->assertSame(
            [
                'missing_required' => [],
                'missing_optional' => [],
            ],
            $validator->getErrors()
        );
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testValidateThrowsException(
        array $rules,
        array $examResultsData,
        array $expectedErrors
    ): void {

        $course = $this->createMock(Course::class);

        $course->method('getUniversity')
            ->willReturn('egyetem');

        $course->method('getFaculty')
            ->willReturn('kar');

        $course->method('getName')
            ->willReturn('szak');

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('base_points.egyetem.kar.szak')
            ->willReturn($rules);

        $results = [];

        foreach ($examResultsData as $data) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($data['name']);

            $result->method('getType')
                ->willReturn($data['type']);

            $result->method('getValue')
                ->willReturn($data['value']);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getCourse')
            ->willReturn($course);

        $applicant->method('getExamResults')
            ->willReturn($results);

        $validator = new RequiredAndOptionalSubjectByMajorValidator($config);

        try {

            $validator->validate($applicant);

            $this->fail('Expected RuntimeException was not thrown');

        } catch (\RuntimeException $e) {

            $this->assertSame(
                'REQUIRED_AND_OPTIONAL_SUBJECT_VALIDATION_ERROR',
                $e->getMessage()
            );

            $this->assertSame(
                $expectedErrors,
                $validator->getErrors()
            );
        }
    }

    public function testThrowsWhenRuleNotFound(): void
    {
        $course = $this->createMock(Course::class);

        $course->method('getUniversity')
            ->willReturn('egyetem');

        $course->method('getFaculty')
            ->willReturn('kar');

        $course->method('getName')
            ->willReturn('szak');

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->willReturn(null);

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getCourse')
            ->willReturn($course);

        $validator = new RequiredAndOptionalSubjectByMajorValidator($config);

        $this->expectException(\RuntimeException::class);

        $this->expectExceptionMessage(
            'RULE_NOT_FOUND_FOR_THE_MAJOR'
        );

        $validator->validate($applicant);
    }
}