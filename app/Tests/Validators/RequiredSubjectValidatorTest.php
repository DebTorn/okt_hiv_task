<?php

namespace Tests\Unit\Validators;

use App\Models\Applicant;
use App\Models\ExamResult;
use App\Providers\Config;
use App\Validators\RequiredSubjectValidator;
use PHPUnit\Framework\TestCase;

class RequiredSubjectValidatorTest extends TestCase
{

    public static function validProvider(): array
    {
        return [
            'all required subjects' => [
                'requiredSubjects' => [
                    'matematika',
                    'történelem',
                ],
                'examSubjects' => [
                    'matematika',
                    'történelem',
                    'informatika',
                ],
            ],

            'required subjects only' => [
                'requiredSubjects' => [
                    'matematika',
                ],
                'examSubjects' => [
                    'matematika',
                ],
            ],

            'empty required subjects' => [
                'requiredSubjects' => [],
                'examSubjects' => [],
            ],
        ];
    }

    public static function invalidProvider(): array
    {
        return [

            'single missing subject' => [
                'requiredSubjects' => [
                    'matematika',
                    'történelem',
                ],
                'examSubjects' => [
                    'matematika',
                ],
                'expectedErrors' => [
                    'történelem',
                ],
            ],

            'multiple missing subjects' => [
                'requiredSubjects' => [
                    'matematika',
                    'történelem',
                    'informatika',
                ],
                'examSubjects' => [
                    'matematika',
                ],
                'expectedErrors' => [
                    'történelem',
                    'informatika',
                ],
            ],

            'all subjects missing' => [
                'requiredSubjects' => [
                    'matematika',
                ],
                'examSubjects' => [],
                'expectedErrors' => [
                    'matematika',
                ],
            ],
        ];
    }

    /**
     * @dataProvider validProvider
     */
    public function testValidatePasses(
        array $requiredSubjects,
        array $examSubjects
    ): void {

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('required_subjects')
            ->willReturn($requiredSubjects);

        $results = [];

        foreach ($examSubjects as $subject) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($subject);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($results);

        $validator = new RequiredSubjectValidator($config);

        $validator->validate($applicant);

        $this->assertSame(
            [
                'missing_subjects' => [],
            ],
            $validator->getErrors()
        );
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testValidateThrowsException(
        array $requiredSubjects,
        array $examSubjects,
        array $expectedErrors
    ): void {

        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('required_subjects')
            ->willReturn($requiredSubjects);

        $results = [];

        foreach ($examSubjects as $subject) {

            $result = $this->createMock(ExamResult::class);

            $result->method('getName')
                ->willReturn($subject);

            $results[] = $result;
        }

        $applicant = $this->createMock(Applicant::class);

        $applicant->method('getExamResults')
            ->willReturn($results);

        $validator = new RequiredSubjectValidator($config);

        try {

            $validator->validate($applicant);

            $this->fail('Expected RuntimeException was not thrown');

        } catch (\RuntimeException $e) {

            $this->assertSame(
                'REQUIRED_SUBJECT_VALIDATION_ERROR',
                $e->getMessage()
            );

            $this->assertSame(
                [
                    'missing_subjects' => $expectedErrors,
                ],
                $validator->getErrors()
            );
        }
    }
}