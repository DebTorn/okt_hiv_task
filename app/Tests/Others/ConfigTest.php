<?php

namespace Tests\Unit\Others;

use App\Enums\ExamType;
use App\Providers\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new Config();

        $this->config->setConfigArray([
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
        ]);
    }

    public function testReturnsDeepMajorStructure(): void
    {
        $result = $this->config->get(
            'base_points.ELTE.IK.Programtervező informatikus'
        );

        $this->assertIsArray($result);

        $this->assertArrayHasKey('required', $result);
        $this->assertArrayHasKey('optional', $result);
    }

    public function testConvertsRequiredToEnumArray(): void
    {
        $result = $this->config->get(
            'base_points.ELTE.IK.Programtervező informatikus.required'
        );

        $this->assertIsArray($result);

        foreach ($result as $type) {
            $this->assertInstanceOf(ExamType::class, $type);
            $this->assertSame(ExamType::MID, $type);
        }
    }

    public function testConvertsOptionalToEnumArray(): void
    {
        $result = $this->config->get(
            'base_points.PPKE.BTK.Anglisztika.optional'
        );

        $this->assertIsArray($result);

        foreach ($result as $type) {
            $this->assertInstanceOf(ExamType::class, $type);
            $this->assertTrue(
                in_array($type, [ExamType::MID, ExamType::ADVANCED], true)
            );
        }
    }

    public function testReadsScalarValues(): void
    {
        $this->assertSame(
            100,
            $this->config->get('limits.max_extra_point')
        );

        $this->assertSame(
            40,
            $this->config->get('extra_points.language_exam.C1')
        );
    }

    public function testReturnsRequiredSubjectList(): void
    {
        $result = $this->config->get('required_subjects');

        $this->assertSame(
            [
                'magyar nyelv és irodalom',
                'történelem',
                'matematika'
            ],
            $result
        );
    }

    public function testReturnsDefaultWhenMissing(): void
    {
        $this->assertSame(
            'default',
            $this->config->get('non.existing.key', 'default')
        );
    }

    public function testReturnsNullByDefault(): void
    {
        $this->assertNull(
            $this->config->get('non.existing.key')
        );
    }
}