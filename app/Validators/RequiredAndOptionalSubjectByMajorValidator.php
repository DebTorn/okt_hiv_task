<?php

namespace App\Validators;

use App\Models\Applicant;
use App\Providers\Config;

class RequiredAndOptionalSubjectByMajorValidator implements Interfaces\ValidatorInterface
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @var array[] $errors
     */
    private array $errors;

    /**
     * @param Config $_config
     */
    public function __construct(
        Config $_config
    ) {
        $this->config = $_config;
        $this->errors = [
            'missing_required' => [],
            'missing_optional' => [],
        ];
    }

    /**
     * @param Applicant $applicant
     * @return void
     */
    public function validate(
        Applicant $applicant
    ): void {

        $course = $applicant->getCourse();

        $rules = $this->config->get(
            sprintf(
                'base_points.%s.%s.%s',
                $course->getUniversity(),
                $course->getFaculty(),
                $course->getName()
            )
        );

        if (!$rules) {
            throw new \RuntimeException(
                'RULE_NOT_FOUND_FOR_THE_MAJOR'
            );
        }

        $required = $rules['required'];
        $optional = $rules['optional'];

        $results = $applicant->getExamResults();

        $foundRequired = [];
        $hasOptional = false;

        foreach ($results as $result) {

            $name = $result->getName();
            $type = $result->getType();

            //Kötelező
            if (isset($required[$name])) {

                $requiredType = $required[$name];

                if ($type->strength() >= $requiredType->strength()) {
                    $foundRequired[$name] = true;
                }
            }

            //Kötelezően választott
            if (isset($optional[$name])) {
                $optionalType = $optional[$name];

                if ($type->strength() >= $optionalType->strength()) {
                    $hasOptional = true;
                }
            }
        }

        foreach ($required as $subject => $type) {
            if (!isset($foundRequired[$subject])) {
                $this->errors['missing_required'][] = $subject;
            }
        }

        if (!$hasOptional) {
            $this->errors['missing_optional'] = $optional;
        }

        if(
            !empty($this->errors['missing_required']) ||
            !empty($this->errors['missing_optional'])
        ){
            throw new \RuntimeException('REQUIRED_AND_OPTIONAL_SUBJECT_VALIDATION_ERROR');
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