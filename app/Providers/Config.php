<?php

namespace App\Providers;

use App\Enums\ExamType;

class Config
{
    /**
     * @var array
     */
    private array $config = [];

    /**
     * @param array $data
     * @return void
     */
    public function setConfigArray(array $data): void
    {
        $this->config = $data;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);

        $value = $this->config;

        foreach ($parts as $part) {

            if (
                !is_array($value) ||
                !array_key_exists($part, $value)
            ) {
                return $default;
            }

            $value = $value[$part];
        }

        return $this->normalize($value);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function normalize(mixed $value): mixed
    {
        // String enum konverzió
        if (is_string($value)) {

            $examType = ExamType::tryFrom($value);

            if ($examType !== null) {
                return $examType;
            }

            return $value;
        }

        if (is_array($value)) {

            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalize($item);
            }

            return $normalized;
        }

        return $value;
    }
}