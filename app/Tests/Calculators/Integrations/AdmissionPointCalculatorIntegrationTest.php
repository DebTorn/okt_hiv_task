<?php

namespace app\Tests\Calculators\Integrations;

use App\Calculators\AdmissionPointCalculator;
use App\Calculators\BasePointCalculator;
use App\Calculators\ExamBonusCalculator;
use App\Calculators\ExtraPointCalculator;
use App\Calculators\Rules\AdvancedExamCalculationRule;
use App\Calculators\Rules\LanguageExamCalculationRule;
use App\Factories\ApplicantFactory;
use App\Providers\Config;
use App\Validators\MinimumPercentageValidator;
use App\Validators\RequiredAndOptionalSubjectByMajorValidator;
use App\Validators\RequiredSubjectValidator;
use PHPUnit\Framework\TestCase;

class AdmissionPointCalculatorIntegrationTest extends TestCase
{
    private const array CONFIG_ARRAY = [
        'required_subjects' => [
            'magyar nyelv és irodalom',
            'történelem',
            'matematika'
        ],
        'limits' => [
            'max_extra_point' => 100,
            'min_percentage' => 20
        ],
        'extra_points' => [
            'language_exam' => [
                'B2' => 28,
                'C1' => 40
            ],
            'advanced_exam' => 50
        ],
        'base_points' => [
            'ELTE' => [
                'IK' => [
                    'Programtervező informatikus' => [
                        'required' => [
                            'matematika' => 'közép',
                        ],

                        'optional' => [
                            'informatika' => 'közép',
                            'fizika' => 'közép',
                            'angol nyelv' => 'közép',
                        ],
                    ]
                ]
            ],
            'PPKE' => [
                'BTK' => [
                    'Anglisztika' => [
                        'required' => [
                            'angol nyelv' => 'emelt'
                        ],
                        'optional' => [
                            'francia nyelv' => 'közép',
                            'német nyelv' => 'közép',
                            'olasz nyelv' => 'közép',
                            'orosz nyelv' => 'közép',
                            'spanyol nyelv' => 'közép',
                            'történelem' => 'közép'
                        ]
                    ]
                ]
            ]
        ]
    ];
    private Config $config;
    private ApplicantFactory $applicantFactory;
    private AdmissionPointCalculator $admissionPointCalculator;

    public function setUp(): void
    {
        $this->applicantFactory = new ApplicantFactory();
        $this->config = new Config();
        $this->config->setConfigArray(self::CONFIG_ARRAY);

        $this->admissionPointCalculator = new AdmissionPointCalculator(
            $this->config,
            [
                new MinimumPercentageValidator($this->config),
                new RequiredSubjectValidator($this->config),
                new RequiredAndOptionalSubjectByMajorValidator($this->config),
            ],
            new BasePointCalculator($this->config),
            new ExamBonusCalculator([
                new AdvancedExamCalculationRule($this->config)
            ]),
            new ExtraPointCalculator($this->config, [
                new LanguageExamCalculationRule($this->config)
            ])
        );
    }

    public static function positiveCasesDataProvider(): array
    {
        return [
            'good elte 1' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '70%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_base_point' => 370,
                'expected_extra_point' => 100,
                'expected_sum' => 470
            ],
            'good elte 2' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '70%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                        [
                            'nev' => 'fizika',
                            'tipus' => 'közép',
                            'eredmeny' => '98%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_base_point' => 376,
                'expected_extra_point' => 100,
                'expected_sum' => 476
            ],
            'good ppke' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'PPKE',
                        'kar' => 'BTK',
                        'szak' => 'Anglisztika',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '70%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'közép',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'emelt',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'német nyelv',
                            'tipus' => 'emelt',
                            'eredmeny' => '75%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                        [
                            'nev' => 'fizika',
                            'tipus' => 'közép',
                            'eredmeny' => '98%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_base_point' => 348,
                'expected_extra_point' => 100,
                'expected_sum' => 448
            ],
        ];
    }

    public static function negativeCasesDataProvider()
    {
        return [
            'bad elte 1 - missing required subject' => [
                'data' => [
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
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_exception' => \RuntimeException::class,
                'expected_exception_message' => 'REQUIRED_SUBJECT_VALIDATION_ERROR'
            ],
            'bad elte 2 - minimum percentage exception' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'ELTE',
                        'kar' => 'IK',
                        'szak' => 'Programtervező informatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '15%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_exception' => \RuntimeException::class,
                'expected_exception_message' => 'MINIMUM_PERCENTAGE_VALIDATION_ERROR'
            ],
            'bad ppke 1 - missing all required optional subjects' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'PPKE',
                        'kar' => 'BTK',
                        'szak' => 'Anglisztika',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '30%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_exception' => \RuntimeException::class,
                'expected_exception_message' => 'REQUIRED_AND_OPTIONAL_SUBJECT_VALIDATION_ERROR'
            ],
            'bad ekke - rule not found' => [
                'data' => [
                    'valasztott-szak' => [
                        'egyetem' => 'EKKE',
                        'kar' => 'IK',
                        'szak' => 'Mérnökinformatikus',
                    ],
                    'erettsegi-eredmenyek' => [
                        [
                            'nev' => 'magyar nyelv és irodalom',
                            'tipus' => 'közép',
                            'eredmeny' => '30%',
                        ],
                        [
                            'nev' => 'történelem',
                            'tipus' => 'közép',
                            'eredmeny' => '80%',
                        ],
                        [
                            'nev' => 'matematika',
                            'tipus' => 'emelt',
                            'eredmeny' => '90%',
                        ],
                        [
                            'nev' => 'angol nyelv',
                            'tipus' => 'közép',
                            'eredmeny' => '94%',
                        ],
                        [
                            'nev' => 'informatika',
                            'tipus' => 'közép',
                            'eredmeny' => '95%',
                        ],
                    ],
                    'tobbletpontok' => [
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'B2',
                            'nyelv' => 'angol',
                        ],
                        [
                            'kategoria' => 'Nyelvvizsga',
                            'tipus' => 'C1',
                            'nyelv' => 'német',
                        ],
                    ],
                ],
                'expected_exception' => \RuntimeException::class,
                'expected_exception_message' => 'RULE_NOT_FOUND_FOR_THE_MAJOR'
            ],
        ];
    }

    /**
     * @dataProvider positiveCasesDataProvider
     */
    public function testPositiveCases(
        $data,
        $expectedBasePoint,
        $expectedExtraPoint,
        $expectedSum,
    ): void
    {
        $applicant = $this->applicantFactory->create($data);

        $sum = $this->admissionPointCalculator->calculate($applicant);

        $this->assertSame(
            $this->admissionPointCalculator->getBasePoints(),
            $expectedBasePoint
        );

        $this->assertSame(
            $this->admissionPointCalculator->getFullBonusPoints(),
            $expectedExtraPoint
        );

        $this->assertSame(
            $sum,
            $expectedSum
        );
    }

    /**
     * @dataProvider negativeCasesDataProvider
     */
    public function testNegativeCases(
        $data,
        $expectedException,
        $expectedExceptionMessage,
    ): void
    {
        $applicant = $this->applicantFactory->create($data);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->admissionPointCalculator->calculate($applicant);
    }
}
