<?php

namespace Tests\Unit\Validators;

use App\Enums\ExamType;
use App\Models\Applicant;
use App\Models\ExamResult;
use App\Providers\Config;
use App\Validators\MinimumPercentageValidator;
use PHPUnit\Framework\TestCase;

class MinimumPercentageValidatorTest extends TestCase
{

    public static function validProvider(): array
    {
        return [

            'all results above minimum' => [
                'resultsData' => [
                    [
                        'name' => 'math',
                        'value' => 50,
                    ],
                    [
                        'name' => 'history',
                        'value' => 80,
                    ],
                ],
                'minimum' => 40,
            ],

            'results equal minimum' => [
                'resultsData' => [
                    [
                        'name' => 'math',
                        'value' => 40,
                    ],
                ],
                'minimum' => 40,
            ],

            'empty results' => [
                'resultsData' => [],
                'minimum' => 40,
            ],

            'REAL_TEST_CASE_1' => [
                'resultsData' => [
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
                ],
                'minimum' => 20,
            ],
            'REAL_TEST_CASE_2' => [
                'resultsData' => [
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
                        'type' => ExamType::MID,
                        'value' => 95,
                    ],
                    [
                        'name' => 'fizika',
                        'type' => ExamType::MID,
                        'value' => 98,
                    ],
                ],
                'minimum' => 20
            ],
            'REAL_TEST_CASE_3' => [
                'resultsData' => [
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
                'minimum' => 20
            ]
        ];
    }

    public static function invalidProvider(): array
    {
        return [

            'single failing subject' => [
                'resultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::MID,
                        'value' => 30,
                    ],
                ],
                'minimum' => 40,
                'expectedErrors' => [
                    [
                        'subject' => 'matematika',
                        'percentage' => 30,
                    ],
                ],
            ],

            'multiple failing subjects' => [
                'resultsData' => [
                    [
                        'name' => 'matematika',
                        'type' => ExamType::MID,
                        'value' => 30,
                    ],
                    [
                        'name' => 'történelem',
                        'type' => ExamType::MID,
                        'value' => 20,
                    ],
                ],
                'minimum' => 40,
                'expectedErrors' => [
                    [
                        'subject' => 'matematika',
                        'percentage' => 30,
                    ],
                    [
                        'subject' => 'történelem',
                        'percentage' => 20,
                    ],
                ],
            ],

            'REAL_TEST_CASE_4' => [
                    'resultsData' => [
                        [
                            'name' => 'magyar nyelv és irodalom',
                            'type' => ExamType::MID,
                            'value' => 15,
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
                    ],
                    'minimum' => 20,
                    'expectedErrors' => [
                        [
                            'subject' => 'magyar nyelv és irodalom',
                            'percentage' => 15,
                        ],
                    ],
            ]
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testValidatePasses(
        array $resultsData,
        int $minimum
    ): void {

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('limits.min_percentage')
            ->willReturn($minimum);

        $validator = new MinimumPercentageValidator($config);

        $results = [];

        foreach ($resultsData as $data) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($data['name']);

            $result->method('getValue')
                ->willReturn($data['value']);

            $result->method('getValue')
                ->willReturn($data['value']);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($results);

        $validator->validate($applicant);

        $this->assertSame(
            ['minimum_percentage_subjects' => []],
            $validator->getErrors()
        );
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testValidateThrowsException(
        array $resultsData,
        int $minimum,
        array $expectedErrors
    ): void {

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('limits.min_percentage')
            ->willReturn($minimum);

        $validator = new MinimumPercentageValidator($config);

        $results = [];

        foreach ($resultsData as $data) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($data['name']);

            $result->method('getValue')
                ->willReturn($data['value']);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($results);

        try {

            $validator->validate($applicant);

            $this->fail('Expected RuntimeException was not thrown');

        } catch (\RuntimeException $e) {

            $this->assertSame(
                'MINIMUM_PERCENTAGE_VALIDATION_ERROR',
                $e->getMessage()
            );

            $this->assertSame(
                [
                    'minimum_percentage_subjects' => $expectedErrors
                ],
                $validator->getErrors()
            );
        }
    }
}