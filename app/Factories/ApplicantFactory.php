<?php

namespace App\Factories;

use App\Enums\ExamType;
use App\Enums\ExtraPointCategory;
use App\Models\Applicant;
use App\Models\Course;
use App\Models\ExamResult;
use App\Models\ExtraPoint;

class ApplicantFactory
{
    /**
     * Létrehoz egy új Applicant-ot egy beérkezett jelentkezés alapján
     *
     * @param array $data
     * @return Applicant
     */
    public function create(array $data)
    {
        //Course létrehozása választott szakból
        $course = new Course(
            $data['valasztott-szak']['egyetem'],
            $data['valasztott-szak']['kar'],
            $data['valasztott-szak']['szak'],
        );

        //ExamResult generálása érettségi eredményekből
        $examResults = [];
        foreach ($data['erettsegi-eredmenyek'] as $result){
            $examTypeEnum = ExamType::tryFrom($result['tipus']);

            if($examTypeEnum === null){
                throw new \RuntimeException('EXAM_TYPE_NOT_EXISTS');
            }

            $examResults[] = new ExamResult(
                $result['nev'],
                $examTypeEnum,
                $this->convertPercentage( //Százalékos eredmény integerré alakítása
                    $result['eredmeny']
                )
            );
        }

        //ExtraPoint létrehozása többletpontokból
        $extraPoints = [];
        foreach($data['tobbletpontok'] as $extraPoint){
            $category = strtolower($extraPoint['kategoria']);
            $categoryEnum = ExtraPointCategory::tryFrom($category);
            if($categoryEnum === null){
                throw new \RuntimeException('CATEGORY_NOT_EXISTS');
            }

            unset($extraPoint['kategoria']);
            $extraPoints[] = new ExtraPoint(
                $categoryEnum,
                $extraPoint
            );
        }

        //Végül Applicant legyártása kurzus-, érettségi- és többletpontokkal
        return new Applicant(
            $course,
            $examResults,
            $extraPoints
        );
    }

    /**
     * @param string $percentageStr
     * @return int
     */
    private function convertPercentage(string $percentageStr): int
    {
        return (int) str_replace('%', '', $percentageStr);
    }
}