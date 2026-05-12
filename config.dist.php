<?php

return [
    'required_subjects' => [
        'magyar nyelv és irodalom',
        'történelem',
        'matematika'
    ],
    'limits' => [
        'max_extra_point' => 100,
        'min_percentage' => 20
    ],
    'extra_points' => [
        'language_exam' => [
            'B2' => 28,
            'C1' => 40
        ],
        'advanced_exam' => 50
    ],
    'base_points' => [
        'ELTE' => [
            'IK' => [
                'Programtervező informatikus' => [
                    'required' => [
                        'matematika' => 'közép',
                    ],

                    'optional' => [
                        'informatika' => 'közép',
                        'fizika' => 'közép',
                        'angol nyelv' => 'közép',
                    ],
                ]
            ]
        ],
        'PPKE' => [
            'BTK' => [
                'Anglisztika' => [
                    'required' => [
                        'angol nyelv' => 'emelt'
                    ],
                    'optional' => [
                        'francia nyelv' => 'közép',
                        'német nyelv' => 'közép',
                        'olasz nyelv' => 'közép',
                        'orosz nyelv' => 'közép',
                        'spanyol nyelv' => 'közép',
                        'történelem' => 'közép'
                    ]
                ]
            ]
        ]
    ]
];