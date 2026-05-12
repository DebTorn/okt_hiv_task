<?php

namespace App\Models;

use App\Enums\ExamType;

class ExamResult
{
    /**
     * @var string
     */
    private string $name;

    /**
     * @var ExamType
     */
    private ExamType $type;

    /**
     * @var int
     */
    private int $value;

    /**
     * @param string $name
     * @param ExamType $type
     * @param int $value
     */
    public function __construct(
        string $name,
        ExamType $type,
        int $value
    )
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return ExamType
     */
    public function getType(): ExamType
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}