<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\TypeC;
use Saturio\DuckDB\Type\UUID;
use Unit\Helper\PartiallyMockedFFITrait;

class UUIDConverterTest extends TestCase
{
    use PartiallyMockedFFITrait;
    private FFIDuckDB $ffi;
    private TypeConverter $converter;

    public function setUp(): void
    {
        parent::setUp();
        $this->ffi = $this->getPartiallyMockedFFI();
        $this->converter = new TypeConverter($this->ffi, MathLib::create());
    }

    #[DataProvider('uuidProvider')]
    public function testFromUUID($hugeintValues, $uuid, $expectedUpperForUHugeInt): void
    {
        $this->ffi
            ->expects(self::once())
            ->method('createUUID')
            ->willReturnArgument(0);

        $uHugeInt = $this->converter->createFromUUID((string) $uuid);
        self::assertEquals($hugeintValues->lower, $uHugeInt->lower);
        self::assertEquals($expectedUpperForUHugeInt, $uHugeInt->upper);
    }

    #[DataProvider('uuidProvider')]
    public function testFromUHugeInt($hugeintValues, $uuid, $expectedUpperForUHugeInt): void
    {
        $hugeint = $this->ffi->new(TypeC::DUCKDB_TYPE_HUGEINT->value);
        $hugeint->lower = $hugeintValues->lower;
        $hugeint->upper = $hugeintValues->upper;
        $returnedUUID = $this->converter->getUUIDFromDuckDBHugeInt($hugeint);
        $this->assertEquals($uuid, $returnedUUID);
    }

    public static function uuidProvider(): array
    {
        return [
            [
                'hugeintValues' => new class {
                    public $lower = -8866597835218230275;
                    public $upper = 186819811232906;
                },
                'uuid' => new UUID('8000a9e9-607c-4c8a-84f3-843f0191e3fd'),
                'expectedUpperForUHugeInt' => -9223185217043542902,
            ],
            [
                'hugeintValues' => new class {
                    public $lower = -1;
                    public $upper = -1;
                },
                'uuid' => new UUID('7fffffff-ffff-ffff-ffff-ffffffffffff'),
                'expectedUpperForUHugeInt' => 9223372036854775807,
            ],
            [
                'hugeintValues' => new class {
                    public $lower = 0;
                    public $upper = 0;
                },
                'uuid' => new UUID('80000000-0000-0000-0000-000000000000'),
                'expectedUpperForUHugeInt' => -9223372036854775808,
            ]];
    }
}
