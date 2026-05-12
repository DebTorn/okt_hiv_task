<?php

namespace App\Validators;

use App\Models\Applicant;
use App\Providers\Config;

class MinimumPercentageValidator implements Interfaces\ValidatorInterface
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @var array[] $errors
     */
    private array $errors = [
        'minimum_percentage_subjects' => []
    ];

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
     * @return void
     */
    public function validate(Applicant $applicant): void
    {
        $min = $this->config->get(
            'limits.min_percentage',
        );

        foreach (
            $applicant->getExamResults()
            as $result
        ) {

            if ($result->getValue() < $min) {
                $this->errors['minimum_percentage_subjects'][] = [
                    'subject' => $result->getName(),
                    'percentage' => $result->getValue()
                ];
            }
        }

        if(!empty($this->errors['minimum_percentage_subjects'])){
            throw new \RuntimeException('MINIMUM_PERCENTAGE_VALIDATION_ERROR');
        }
    }

    /**
     * @return array[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}