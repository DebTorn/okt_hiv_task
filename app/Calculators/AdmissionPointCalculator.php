<?php

namespace App\Calculators;

use App\Models\Applicant;
use App\Validators\Interfaces\ValidatorInterface;
use App\Providers\Config;

class AdmissionPointCalculator
{
    private Config $config;

    /**
     * @var ValidatorInterface[]
     */
    private array $validators;

    /**
     * @var BasePointCalculator $basePointCalculator
     */
    private BasePointCalculator $basePointCalculator;

    /**
     * @var ExamBonusCalculator $examBonusCalculator
     */
    private ExamBonusCalculator $examBonusCalculator;

    /**
     * @var ExtraPointCalculator $extraPointCalculator
     */
    private ExtraPointCalculator $extraPointCalculator;

    /**
     * Számítás során kiszámolt alappontok
     *
     * @var int
     */
    private int $basePoints;

    /**
     * Számítás során kiszámolt többletpontok emelt érettségi pontok kivételével
     *
     * @var int
     */
    private int $extraPoints;

    /**
     * Számítás során kiszámolt emelt érettségikből pontok
     *
     * @var int
     */
    private int $separatedBonusPoints;

    /**
     * Számítás során kiszámolt többlet- és emelt érettségi pontok összesítve
     *
     * @var int
     */
    private int $fullBonusPoints;

    /**
     * @param Config $_config
     * @param ValidatorInterface[] $_validators
     * @param BasePointCalculator $_baseCalculator
     * @param ExamBonusCalculator $_examBonusCalculator
     * @param ExtraPointCalculator $_extraCalculator
     */
    public function __construct(
        Config $_config,
        array $_validators,
        BasePointCalculator $_baseCalculator,
        ExamBonusCalculator $_examBonusCalculator,
        ExtraPointCalculator $_extraCalculator
    ) {
        $this->config = $_config;
        $this->validators = $_validators;
        $this->basePointCalculator = $_baseCalculator;
        $this->examBonusCalculator = $_examBonusCalculator;
        $this->extraPointCalculator = $_extraCalculator;
    }

    /**
     * @param Applicant $applicant
     * @return int
     */
    public function calculate(Applicant $applicant): int
    {
        //Validációk futtatása
        foreach ($this->validators as $validator) {
            $validator->validate($applicant);
        }

        //Alappontok kiszámítása
        $basePoints = $this->basePointCalculator->calculate($applicant);
        $this->basePoints = $basePoints;

        //Emelt érettségi pontok kiszámítása
        $bonusPoints = $this->examBonusCalculator->calculate($applicant);
        $this->separatedBonusPoints = $bonusPoints;

        //Többletpontok kiszámítása
        $extraPoints = $this->extraPointCalculator->calculate(
            $applicant->getExtraPoints()
        );
        $this->extraPoints = $extraPoints;

        //Pluszpontok összesítése
        $sumBonuses = $bonusPoints + $extraPoints;

        //Ha elértük a maximum pontot, akkor a limits.max_extra_point lesz az értékünk
        $maxPoint = $this->config->get('limits.max_extra_point');
        if($sumBonuses > $maxPoint){
            $sumBonuses = $maxPoint;
        }

        $this->fullBonusPoints = $sumBonuses;

        //Végösszeg meghatározása alap- és összesített pluszpontokból
        return $basePoints + $sumBonuses;
    }

    /**
     * @return int
     */
    public function getBasePoints(): int
    {
        return $this->basePoints;
    }

    /**
     * @return int
     */
    public function getExtraPoints(): int
    {
        return $this->extraPoints;
    }

    /**
     * @return int
     */
    public function getSeparatedBonusPoints(): int
    {
        return $this->separatedBonusPoints;
    }

    /**
     * @return int
     */
    public function getFullBonusPoints(): int
    {
        return $this->fullBonusPoints;
    }
}