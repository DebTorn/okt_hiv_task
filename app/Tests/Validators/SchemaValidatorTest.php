<?php

use PHPUnit\Framework\TestCase;
use App\Validators\SchemaValidator;
use App\Validators\Schemas\ApplicantSchema;

class SchemaValidatorTest extends TestCase
{
    private SchemaValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new SchemaValidator();
        $this->validator->setSchema(ApplicantSchema::get());
    }

    /**
     * @return array[]
     */
    public static function applicantProvider(): array
    {
        return [
            'valid applicant' => [
                [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ]
                    ],
                ],
                true,
                []
            ],

            'missing erettsegi-eredmenyek' => [
                [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'tobbletpontok' => [],
                ],
                false,
                [
                    [
                        'code' => 'MISSING_FIELD',
                        'path' => 'erettsegi-eredmenyek',
                    ]
                ]
            ],

            'invalid erettsegi tipus' => [
                [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'matematika',
                            'tipus' => 'alap',
                            'eredmeny' => '90%',
                        ]
                    ],
                    'tobbletpontok' => [],
                ],
                false,
                [
                    [
                        'code' => 'INVALID_VALUE',
                        'path' => 'erettsegi-eredmenyek.0.tipus',
                    ]
                ]
            ],

            'missing lang for language exam' => [
                [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ]
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                        ]
                    ],
                ],
                false,
                [
                    [
                        'code' => 'MISSING_FIELD',
                        'path' => 'tobbletpontok.0.nyelv',
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider applicantProvider
     */
    public function testApplicantSchema(array $data, bool $expected, array $expectedErrors = []): void
    {
        $result = $this->validator->validate($data);

        $this->assertSame($expected, $result);

        $errors = $this->validator->getErrors();

        if (!$expected) {
            $this->assertNotEmpty($errors);
            $this->assertEquals($expectedErrors, $errors);
        } else {
            $this->assertEmpty($errors);
        }
    }
}