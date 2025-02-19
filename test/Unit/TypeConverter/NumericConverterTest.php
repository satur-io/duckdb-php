<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Converter\NumericConverter;
use Unit\Helper\DummyCData;

class NumericConverterTest extends TestCase
{
    private FFIDuckDB $ffi;
    private NumericConverter $converter;

    public function setUp(): void
    {
        $this->ffi = $this->createMock(FFIDuckDB::class);
    }

    public function testFloatFromDecimalComingAsInteger()
    {
        $decimal = 1000;

        $this->ffi
            ->expects(self::once())
            ->method('doubleToHugeint')
            ->willReturn(new class('This is the CData of a Hugeint') extends DummyCData {
                public function __construct(public $cdata)
                {
                }
            });

        $this->floatFromDecimalAssertions($decimal);
    }

    public function testFloatFromDecimalComingAsHugeint()
    {
        $data = new class('This is the CData of a Hugeint') extends DummyCData {
            public function __construct(public $cdata)
            {
            }
        };

        $this->ffi
            ->expects(self::never())
            ->method('doubleToHugeint');

        $this->floatFromDecimalAssertions($data);
    }

    public function floatFromDecimalAssertions(int|CDataInterface $decimal): void
    {
        $logicalType = new DummyCData();

        $expectedFloat = 1.1;

        $decimalWidth = 20;
        $decimalScale = 25;

        $duckDBTypeDecimal = new class extends DummyCData {
            public $width;
            public $scale;
            public $value;
        };
        $duckDBTypeDecimalFilled = clone $duckDBTypeDecimal;
        $duckDBTypeDecimalFilled->width = $decimalWidth;
        $duckDBTypeDecimalFilled->scale = $decimalScale;
        $duckDBTypeDecimalFilled->value = 'This is the CData of a Hugeint';

        $this->ffi
            ->expects(self::any())
            ->method('new')
            ->with('duckdb_decimal', false)
            ->willReturn($duckDBTypeDecimal);

        $this->ffi
            ->expects(self::any())
            ->method('addr')
            ->willReturn(new DummyCData());

        $this->ffi
            ->expects(self::once())
            ->method('decimalWidth')
            ->with($logicalType)
            ->willReturn($decimalWidth);

        $this->ffi
            ->expects(self::once())
            ->method('decimalScale')
            ->with($logicalType)
            ->willReturn($decimalScale);

        $this->ffi
            ->expects(self::once())
            ->method('decimalToDouble')
            ->with($duckDBTypeDecimalFilled)
            ->willReturn($expectedFloat);

        $converter = new NumericConverter($this->ffi);
        $double = $converter->getFloatFromDecimal(
            $decimal,
            $logicalType,
        );

        self::assertEquals($expectedFloat, $double);
    }
}
