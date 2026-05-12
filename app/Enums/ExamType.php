<?php

namespace App\Enums;

enum ExamType: string
{
    case MID = 'közép';
    case ADVANCED = 'emelt';

    /**
     * Érettségi szint súlyozás későbbi kalkulációkhoz
     *
     * @return int
     */
    public function strength(): int
    {
        return match ($this) {
            self::MID => 1,
            self::ADVANCED => 2,
        };
    }
}