<?php

namespace App\Models;

class Applicant
{
    /**
     * @var Course
     */
    private Course $course;

    /**
     * @var ExamResult[]
     */
    private array $examResults;

    /**
     * @var ExtraPoint[]
     */
    private array $extraPoints;

    /**
     * @param Course $course
     * @param ExamResult[] $examResults
     * @param ExtraPoint[] $extraPoints
     */
    public function __construct(
        Course $course,
        array $examResults,
        array $extraPoints
    )
    {
        $this->course = $course;
        $this->examResults = $examResults;
        $this->extraPoints = $extraPoints;
    }

    /**
     * @return Course
     */
    public function getCourse(): Course
    {
        return $this->course;
    }

    /**
     * @return ExamResult[]
     */
    public function getExamResults(): array
    {
        return $this->examResults;
    }

    /**
     * @return ExtraPoint[]
     */
    public function getExtraPoints(): array
    {
        return $this->extraPoints;
    }
}