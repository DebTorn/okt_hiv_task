<?php

namespace App\Validators\Interfaces;

use App\Models\Applicant;

interface ValidatorInterface
{
    /**
     * @param Applicant $applicant
     * @return void
     */
    public function validate(Applicant $applicant): void;

    /**
     * @return array
     */
    public function getErrors(): array;
}