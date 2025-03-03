<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\UUID;
use Unit\Abstract\TestWithInterfaces;
use Unit\Helper\DummyCData;

#[RunClassInSeparateProcess]
class UUIDConverterTest extends TestWithInterfaces
{
    private FFIDuckDB $ffi;
    private TypeConverter $converter;

    public function setUp(): void
    {
        parent::setUp();
        $this->ffi = $this->createMock(FFIDuckDB::class);
        $this->converter = new TypeConverter($this->ffi, MathLib::create());
    }

    public function testFromUUID(): void
    {
        $uHugeInt = new class extends DummyCData {
            public $lower;
            public $upper;
        };
        $this->ffi
            ->expects(self::exactly(3))
            ->method('new')
            ->willReturn($uHugeInt);

        $this->ffi
            ->expects(self::exactly(3))
            ->method('createUUID')
            ->willReturnCallback(
                function ($uHugeInt) {
                    return new class($uHugeInt) extends DummyCData {
                        public function __construct(public $pointerToUUID)
                        {
                        }
                    };
                }
            );

        array_map(function ($uuidCase) {
            $uHugeInt = $this->converter->createFromUUID($uuidCase[1]);
            self::assertEquals($uuidCase[0]->lower, $uHugeInt->pointerToUUID->lower);
            self::assertEquals($uuidCase['expectedUpperForUHugeInt'], $uHugeInt->pointerToUUID->upper);
        }, self::uuidProvider());
    }

    public function testFromUHugeInt(): void
    {
        array_map(function ($uuidCase) {
            $uuid = $this->converter->getUUIDFromDuckDBHugeInt($uuidCase[0]);
            $this->assertEquals($uuidCase[1], $uuid);
        }, self::uuidProvider());
    }

    public static function uuidProvider(): array
    {
        return [[
            new class extends DummyCData {
                public $lower = -8866597835218230275;
                public $upper = 186819811232906;
            },
            new UUID('8000a9e9-607c-4c8a-84f3-843f0191e3fd'),
            'expectedUpperForUHugeInt' => -9223185217043542902,
        ],
            [
                new class extends DummyCData {
                    public $lower = -1;
                    public $upper = -1;
                },
                new UUID('7fffffff-ffff-ffff-ffff-ffffffffffff'),
                'expectedUpperForUHugeInt' => 9223372036854775807,
            ],
            [
                new class extends DummyCData {
                    public $lower = 0;
                    public $upper = 0;
                },
                new UUID('80000000-0000-0000-0000-000000000000'),
                'expectedUpperForUHugeInt' => -9223372036854775808,
            ]];
    }
}
