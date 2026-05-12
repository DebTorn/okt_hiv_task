<?php

namespace App\Models;

class Course
{
    /**
     * @var string
     */
    private string $university;

    /**
     * @var string
     */
    private string $faculty;

    /**
     * @var string
     */
    private string $name;

    /**
     * @param string $_university
     * @param string $_faculty
     * @param string $_name
     */
    public function __construct(
        string $_university,
        string $_faculty,
        string $_name,
    )
    {
        $this->university = $_university;
        $this->faculty = $_faculty;
        $this->name = $_name;
    }

    /**
     * @return string
     */
    public function getUniversity(): string
    {
        return $this->university;
    }

    /**
     * @return string
     */
    public function getFaculty(): string
    {
        return $this->faculty;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}