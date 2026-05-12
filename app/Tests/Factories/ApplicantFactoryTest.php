<?php

namespace Tests\Unit\Factories;

use App\Enums\ExamType;
use App\Enums\ExtraPointCategory;
use App\Factories\ApplicantFactory;
use App\Models\Applicant;
use PHPUnit\Framework\TestCase;

class ApplicantFactoryTest extends TestCase
{

    public static function createProvider(): array
    {
        return [

            'basic valid applicant' => [
                'input' => [
                    'valasztott-szak' => [
                        'egyetem' => 'uni',
                        'kar' => 'kar',
                        'szak' => 'szak',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '70%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                    ],
                ],
            ],

            'no extra points' => [
                'input' => [
                    'valasztott-szak' => [
                        'egyetem' => 'uni',
                        'kar' => 'kar',
                        'szak' => 'szak',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '100%',
                        ],
                    ],
                    'tobbletpontok' => [],
                ],
            ],
        ];
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreation(array $input): void
    {
        $factory = new ApplicantFactory();

        $applicant = $factory->create($input);

        $this->assertInstanceOf(Applicant::class, $applicant);

        $this->assertSame(
            $input['valasztott-szak']['egyetem'],
            $applicant->getCourse()->getUniversity()
        );

        $this->assertCount(
            count($input['erettsegi-eredmenyek']),
            $applicant->getExamResults()
        );

        $this->assertCount(
            count($input['tobbletpontok']),
            $applicant->getExtraPoints()
        );
    }

    public function testThrowsOninvalidExamType(): void
    {
        $factory = new ApplicantFactory();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('EXAM_TYPE_NOT_EXISTS');

        $factory->create([
            'valasztott-szak' => [
                'egyetem' => 'uni',
                'kar' => 'kar',
                'szak' => 'szak',
            ],
            'erettsegi-eredmenyek' => [
                [
                    'nev' => 'matematika',
                    'tipus' => 'asd',
                    'eredmeny' => '80%',
                ],
            ],
            'tobbletpontok' => [],
        ]);
    }

    public function testThrowsOnInvalidExtraPointCategory(): void
    {
        $factory = new ApplicantFactory();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('CATEGORY_NOT_EXISTS');

        $factory->create([
            'valasztott-szak' => [
                'egyetem' => 'uni',
                'kar' => 'kar',
                'szak' => 'szak',
            ],
            'erettsegi-eredmenyek' => [],
            'tobbletpontok' => [
                [
                    'kategoria' => 'asd',
                    'nyelv' => 'angol',
                    'tipus' => 'B2',
                ],
            ],
        ]);
    }
}