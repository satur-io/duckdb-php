<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Converter\TypeConverter;
use Unit\Helper\DummyCData;

class VarCharConverterTest extends TestCase
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        $this->ffi = $this->createMock(FFIDuckDB::class);
    }

    // This happens when data struct length is <= 12
    public function testInlinedVarchar()
    {
        $inlinedCDataVarChar = $this->toStdClass(
            [
                'value' => [
                    'inlined' => [
                        'length' => 3,
                        'inlined' => new DummyCData(),
                    ],
                    'pointer' => [
                        'length' => 3,
                        'ptr' => new DummyCData(),
                    ],
                ],
            ]);

        $inlinedVarChar = new class($inlinedCDataVarChar) extends DummyCData {
            public function __construct(public $cdata)
            {
            }
        };

        $expectedString = 'The expected string';
        $inlinedVarCharConverted = clone $inlinedVarChar;
        $inlinedVarCharConverted->cdata = $inlinedCDataVarChar->value->inlined->inlined;
        $length = $inlinedCDataVarChar->value->inlined->length;
        $this->ffi
            ->expects(self::once())
            ->method('string')
            ->with($inlinedVarCharConverted, $length)
            ->willReturn($expectedString);

        self::assertEquals($expectedString, TypeConverter::getVarChar($inlinedVarChar, $this->ffi));
    }

    // This happens when data struct length is > 12
    public function testPointerVarchar()
    {
        $inlinedCDataVarChar = $this->toStdClass(
            [
                'value' => [
                    'inlined' => [
                        'length' => 20,
                        'inlined' => new DummyCData(),
                    ],
                    'pointer' => [
                        'length' => 20,
                        'ptr' => new DummyCData(),
                    ],
                ],
            ]);

        $inlinedVarChar = new class($inlinedCDataVarChar) extends DummyCData {
            public function __construct(public $cdata)
            {
            }
        };

        $expectedString = 'The expected string';
        $inlinedVarCharConverted = clone $inlinedVarChar;
        $inlinedVarCharConverted->cdata = $inlinedCDataVarChar->value->pointer->ptr;
        $length = $inlinedCDataVarChar->value->pointer->length;
        $this->ffi
            ->expects(self::once())
            ->method('string')
            ->with($inlinedVarCharConverted, $length)
            ->willReturn($expectedString);

        self::assertEquals($expectedString, TypeConverter::getVarChar($inlinedVarChar, $this->ffi));
    }

    public function toStdClass(array $array): \stdClass
    {
        return json_decode(json_encode($array));
    }
}
