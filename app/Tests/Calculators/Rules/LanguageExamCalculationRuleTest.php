<?php
namespace App\Tests\Calculators\Rules;

use App\Calculators\Rules\LanguageExamCalculationRule;
use App\Enums\ExtraPointCategory;
use App\Models\ExtraPoint;
use App\Providers\Config;
use PHPUnit\Framework\TestCase;

class LanguageExamCalculationRuleTest extends TestCase
{
    public static function calculationProvider(): array
    {
        return [
            'same language picks best level' => [
                'points' => [
                    ['nyelv' => 'angol', 'tipus' => 'B2'],
                    ['nyelv' => 'angol', 'tipus' => 'C1'],
                ],
                'config' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 20,
            ],
            'multiple languages summed' => [
                'points' => [
                    ['nyelv' => 'angol', 'tipus' => 'B2'],
                    ['nyelv' => 'francia', 'tipus' => 'C1'],
                ],
                'config' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 30,
            ],
            'missing config returns zero' => [
                'points' => [
                    ['nyelv' => 'angol', 'tipus' => 'C1'],
                ],
                'config' => [
                    'B2' => 10,
                    //C1 nincs
                ],
                'expected' => 0,
            ],
            'mixed levels per language' => [
                'points' => [
                    ['nyelv' => 'angol', 'tipus' => 'B2'],
                    ['nyelv' => 'angol', 'tipus' => 'C1'],
                    ['nyelv' => 'német', 'tipus' => 'B2'],
                ],
                'config' => [
                    'B2' => 10,
                    'C1' => 20,
                ],
                'expected' => 30, //20 (angol C1) + 10 (német B2)
            ],

            'empty input returns zero' => [
                'points' => [],
                'config' => [
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

        $rule = new LanguageExamCalculationRule($config);

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

        $result = $rule->calculate($points);

        $this->assertEquals($expected, $result);
    }
}