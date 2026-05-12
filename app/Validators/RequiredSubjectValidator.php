<?php

namespace App\Validators;

use App\Models\Applicant;
use App\Models\ExamResult;
use App\Providers\Config;

class RequiredSubjectValidator implements Interfaces\ValidatorInterface
{
    /**
     * @var Config $config
     */
    private Config $config;

    /**
     * @var array[] $errors
     */
    private array $errors = [
        'missing_subjects' => []
    ];

    /**
     * @param Config $_config
     */
    public function __construct(Config $_config)
    {
        $this->config = $_config;
    }

    /**
     * @param Applicant $applicant
     * @return void
     */
    public function validate(Applicant $applicant): void
    {
        $requiredSubjects =
            $this->config->get(
                'required_subjects'
            );

        $subjects = array_map(
            fn(ExamResult $r) => $r->getName(),
            $applicant->getExamResults()
        );

        foreach ($requiredSubjects as $required) {

            if (
                !in_array(
                    $required,
                    $subjects,
                    true
                )
            ) {
                $this->errors['missing_subjects'][] = $required;
            }
        }

        if(!empty($this->errors['missing_subjects'])){
            throw new \RuntimeException('REQUIRED_SUBJECT_VALIDATION_ERROR');
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