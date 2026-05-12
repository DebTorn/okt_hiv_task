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

$results = [];
$notCalculated = [];

foreach ($inputArray as $index => $input) {
    try {
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

        $notCalculated[] = [
            'applicant_index' => $index,
            'error_code' => $errorKey,
            'error_message' => $errorMessages[$errorKey] ?? 'Ismeretlen hiba',
            'applicant' => $input,
        ];
    }
}

echo PHP_EOL;
echo "=== SIKERES SZÁMÍTÁSOK ===" . PHP_EOL;
print_r($results);

echo PHP_EOL;
echo "=== HIBÁS JELENTKEZÉSEK ===" . PHP_EOL;
print_r($notCalculated);