<?php

namespace App\Calculators;

use App\Models\Applicant;
use App\Providers\Config;

class BasePointCalculator
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
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(
        Applicant $applicant
    ): int
    {

        //Applicant-ból kurzus lekérdezése
        $course = $applicant->getCourse();

        //Pontszámítási szabályok lekérdezése egyetem -> kar -> szak kötésből
        $rules = $this->config->get(
            sprintf(
                'base_points.%s.%s.%s',
                $course->getUniversity(),
                $course->getFaculty(),
                $course->getName()
            )
        );

        if (!$rules) {
            throw new \RuntimeException('POINT_RULE_NOT_FOUND');
        }

        //Érettségi eredmények lekérdezése
        $results = $applicant->getExamResults();

        $requiredSum = 0;
        $optionalBest = 0;

        foreach ($results as $result) {

            //Érettésig eredmény részleteinek lekérdezése
            $name = $result->getName();
            $type = $result->getType();
            $value = $result->getValue();


            //Kötelező tárgyak összesítése
            if (isset($rules['required'][$name])) {

                $requiredLevel = $rules['required'][$name];

                if ($type->strength() >= $requiredLevel->strength()) {
                    $requiredSum += $value;
                }
            }

            //Kötelezően választható tárgyak összesítése
            if (isset($rules['optional'][$name])) {

                $optionalLevel = $rules['optional'][$name];

                if ($type->strength() >= $optionalLevel->strength()) {
                    $optionalBest = max(
                        $optionalBest,
                        $value
                    );
                }
            }
        }

        //(Kötelező tárgyak + Kötelezően választható tárgyak) * 2
        return ($requiredSum + $optionalBest) * 2;
    }
}