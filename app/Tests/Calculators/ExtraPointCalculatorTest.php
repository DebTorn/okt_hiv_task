<?php

namespace Tests\Unit\Calculators;

use App\Calculators\ExtraPointCalculator;
use App\Calculators\Rules\LanguageExamCalculationRule;
use App\Enums\ExtraPointCategory;
use App\Models\ExtraPoint;
use App\Providers\Config;
use PHPUnit\Framework\TestCase;

class ExtraPointCalculatorTest extends TestCase
{

    public static function calculationProvider(): array
    {
        return [

            'same language keeps best level' => [
                'pointsData' => [
                    ['nyelv' => 'angol', 'tipus' => 'B2'],
                    ['nyelv' => 'angol', 'tipus' => 'C1'],
                ],
                'configReturn' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 20,
            ],

            'multiple languages summed' => [
                'pointsData' => [
                    ['nyelv' => 'angol', 'tipus' => 'B2'],
                    ['nyelv' => 'magyar', 'tipus' => 'C1'],
                ],
                'configReturn' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 30,
            ],

            'missing config returns zero' => [
                'pointsData' => [
                    ['nyelv' => 'angol', 'tipus' => 'C1'],
                ],
                'configReturn' => [
                    'B2' => 10,
                ],
                'expected' => 0,
            ],

            'empty input returns zero' => [
                'pointsData' => [],
                'configReturn' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 0,
            ],
        ];
    }

    /**
     * @dataProvider calculationProvider
     */
    public function testCalculation(array $pointsData, array $configReturn, int $expected): void
    {
        $config = $this->createMock(Config::class);

        $config->method('get')
            ->with('extra_points.language_exam')
            ->willReturn($configReturn);

        $rules = [
            new LanguageExamCalculationRule($config),
        ];

        $calculator = new ExtraPointCalculator($config, $rules);

        $points = [];

        foreach ($pointsData as $data) {

            $points[] = new ExtraPoint(
                ExtraPointCategory::LANGUAGE_EXAM,
                [
                    'nyelv' => $data['nyelv'],
                    'tipus' => $data['tipus'],
                ]
            );
        }

        $result = $calculator->calculate($points);

        $this->assertSame($expected, $result);
    }
}