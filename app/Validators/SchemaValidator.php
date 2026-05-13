<?php

namespace App\Validators;

class SchemaValidator
{
    private array $errors = [];
    private array $schema = [];

    public function validate(array $data, array $schema = []): bool
    {
        $schema = !empty($this->schema) ? $this->schema : $schema;

        $this->errors = [];

        $this->validateSchema($data, $schema);

        return empty($this->errors);
    }

    public function setSchema(array $schema): void
    {
        $this->schema = $schema;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function validateSchema(
        array $data,
        array $schema,
        string $path = ''
    ): void {

        foreach ($schema as $field => $rules) {

            $currentPath = $path ? "$path.$field" : $field;
            $exists = array_key_exists($field, $data);

            $isRequired = $rules['required'] ?? false;

            if (isset($rules['required_if'])) {

                $condField = $rules['required_if']['field'];
                $condValue = $rules['required_if']['value'];

                if (
                    array_key_exists($condField, $data) &&
                    $data[$condField] === $condValue
                ) {
                    $isRequired = true;
                }
            }

            if (!$exists) {

                if ($isRequired) {
                    $this->errors[] = [
                        'code' => 'MISSING_FIELD',
                        'path' => $currentPath
                    ];
                }

                continue;
            }

            $value = $data[$field];

            $this->validateType($value, $rules['type'], $currentPath);

            if (isset($rules['enum'])) {

                if (!in_array($value, $rules['enum'], true)) {
                    $this->errors[] = [
                        'code' => 'INVALID_VALUE',
                        'path' => $currentPath
                    ];
                }
            }

            if (isset($rules['pattern'])) {

                if (!is_string($value) || !preg_match($rules['pattern'], $value)) {
                    $this->errors[] = [
                        'code' => 'INVALID_FORMAT',
                        'path' => $currentPath
                    ];
                }
            }

            if (
                $rules['type'] === 'array' &&
                isset($rules['schema']) &&
                is_array($value)
            ) {
                $this->validateSchema(
                    $value,
                    $rules['schema'],
                    $currentPath
                );
            }

            if (
                $rules['type'] === 'array' &&
                isset($rules['items']) &&
                is_array($value)
            ) {
                foreach ($value as $index => $item) {

                    if (!is_array($item)) {
                        $this->errors[] = [
                            'code' => 'INVALID_TYPE',
                            'path' => "$currentPath.$index",
                            'expected' => 'array'
                        ];
                        continue;
                    }

                    $this->validateSchema(
                        $item,
                        $rules['items']['schema'],
                        "$currentPath.$index"
                    );
                }
            }
        }
    }

    private function validateType(
        mixed $value,
        string $type,
        string $path
    ): void {

        $valid = match ($type) {
            'string' => is_string($value),
            'array'  => is_array($value),
            'int'    => is_int($value),
            'bool'   => is_bool($value),
            default  => false,
        };

        if (!$valid) {
            $this->errors[] = [
                'code' => 'INVALID_TYPE',
                'path' => $path,
                'expected' => $type
            ];
        }
    }
}