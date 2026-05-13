<?php

require __DIR__ . '/autoload.php';

if (!file_exists(__DIR__ . '/config.php')) {
    throw new RuntimeException('config.php not found');
}

$configArray = require __DIR__ . '/config.php';
$inputArray = require __DIR__ . '/input.php';
$errorMessages = require __DIR__ . '/errors.php';

$applicantFactory = new App\Factories\ApplicantFactory();

$config = new \App\Providers\Config();
$config->setConfigArray($configArray);

$schema = \App\Validators\Schemas\ApplicantSchema::get();
$schemaValidator = new \App\Validators\SchemaValidator();
$schemaValidator->setSchema($schema);

$results = [];
$notCalculated = [];

foreach ($inputArray as $index => $input) {
    try {
        $schemaValidationResult = $schemaValidator->validate($input);

        if($schemaValidationResult === false){
            throw new RuntimeException('SCHEMA_VALIDATION_FAILED');
        }

        $admissionCalculator = new \App\Calculators\AdmissionPointCalculator(
            $config,
            [
                new \App\Validators\MinimumPercentageValidator($config),
                new \App\Validators\RequiredSubjectValidator($config),
                new \App\Validators\RequiredAndOptionalSubjectByMajorValidator($config),
            ],
            new \App\Calculators\BasePointCalculator($config),
            new \App\Calculators\ExamBonusCalculator([
                new \App\Calculators\Rules\AdvancedExamCalculationRule($config)
            ]),
            new \App\Calculators\ExtraPointCalculator($config, [
                new \App\Calculators\Rules\LanguageExamCalculationRule($config),
            ]),
        );

        $applicant = $applicantFactory->create($input);

        $result = $admissionCalculator->calculate($applicant);

        $results[] = [
            'applicant_index' => $index,
            'base_points' => $admissionCalculator->getBasePoints(),
            'extra_points' => $admissionCalculator->getFullBonusPoints(),
            'sum_points' => $result,
        ];

    } catch (RuntimeException $e) {
        $errorKey = $e->getMessage();

        $newError = [
            'applicant_index' => $index,
            'error_code' => $errorKey,
            'error_message' => $errorMessages[$errorKey] ?? 'Ismeretlen hiba',
            'applicant' => $input,
        ];

        if($e->getMessage() === 'SCHEMA_VALIDATION_FAILED'){
            $newError['errors'] = $schemaValidator->getErrors();
        }

        $notCalculated[] = $newError;
    }
}

echo PHP_EOL;
echo "=== SIKERES SZÁMÍTÁSOK ===" . PHP_EOL;
print_r($results);

echo PHP_EOL;
echo "=== HIBÁS JELENTKEZÉSEK ===" . PHP_EOL;
print_r($notCalculated);