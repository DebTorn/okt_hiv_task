<?php
namespace App\Validators\Schemas;

class ApplicantSchema
{
    public static function get(): array
    {
        return [
            'valasztott-szak' => [
                'type' => 'array',
                'required' => true,
                'schema' => [
                    'egyetem' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                    'kar' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                    'szak' => [
                        'type' => 'string',
                        'required' => true,
                    ],
                ],
            ],

            'erettsegi-eredmenyek' => [
                'type' => 'array',
                'required' => true,
                'items' => [
                    'type' => 'array',
                    'schema' => [
                        'nev' => [
                            'type' => 'string',
                            'required' => true,
                        ],
                        'tipus' => [
                            'type' => 'string',
                            'required' => true,
                            'enum' => ['közép', 'emelt'],
                        ],
                        'eredmeny' => [
                            'type' => 'string',
                            'required' => true,
                            'pattern' => '/^\d{1,3}%$/',
                        ],
                    ],
                ],
            ],
            'tobbletpontok' => [
                'type' => 'array',
                'required' => true,
                'allow_empty' => true,
                'items' => [
                    'type' => 'array',
                    'schema' => [

                        'kategoria' => [
                            'type' => 'string',
                            'required' => true,
                        ],

                        'tipus' => [
                            'type' => 'string',
                            'required' => true,
                        ],

                        'nyelv' => [
                            'type' => 'string',
                            'required' => false,
                            'required_if' => [
                                'field' => 'kategoria',
                                'value' => 'Nyelvvizsga',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}