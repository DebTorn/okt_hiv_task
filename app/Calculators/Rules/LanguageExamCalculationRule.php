<?php

namespace App\Calculators\Rules;

use App\Calculators\Interfaces\ExtraPointCalculationRuleInterface;
use App\Enums\ExtraPointCategory;
use App\Models\ExtraPoint;
use App\Providers\Config;

class LanguageExamCalculationRule implements ExtraPointCalculationRuleInterface
{
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param Config $_config
     */
    public function __construct(
        Config $_config
    ) {
        $this->config = $_config;
    }

    /**
     * @inheritDoc
     */
    public function supports(ExtraPoint $point): bool
    {
        return $point->getCategory() === ExtraPointCategory::LANGUAGE_EXAM;
    }

    /**
     * Azonos nyelvből csak a legmagasabb nyelvvizsga számít
     *
     * @inheritDoc
     */
    public function calculate(array $points): int
    {
        $bestLevelsByLanguage = [];

        foreach ($points as $point) {
            $payload = $point->getPayload();

            $language = $payload['nyelv'];
            $level = $payload['tipus'];

            //Első találat során azt vesszük alapul és továbblépünk
            if (
                !isset($bestLevelsByLanguage[$language])
            ) {
                $bestLevelsByLanguage[$language] = $level;
                continue;
            }

            //Ha magasabb mint ugyanazon szinten lévő azonos nyelv, akkor a jelenlegit vesszük alapul
            if (
                $this->weight($level) > $this->weight($bestLevelsByLanguage[$language])
            ) {
                $bestLevelsByLanguage[$language] = $level;
            }
        }

        $pointMap = $this->config->get('extra_points.language_exam');

        $sum = 0;

        //Nyelvenként kiválasztott legmagasabb pontszámok összesítése
        foreach ($bestLevelsByLanguage as $level) {
            $sum += $pointMap[$level] ?? 0;
        }

        return $sum;
    }

    /**
     * Nyelvi szintek súlyozását megállapító segédfüggvény
     *
     * @param string $level
     * @return int
     */
    private function weight(string $level): int {
        return match ($level) {
            'B2' => 1,
            'C1' => 2,
            default => 0,
        };
    }
}