<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Converter\NumericConverter;
use Saturio\DuckDB\Type\Math\LongInteger;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\TypeC;
use Unit\Helper\PartiallyMockedFFITrait;

class NumericConverterTest extends TestCase
{
    use PartiallyMockedFFITrait;
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        $this->ffi = $this->getPartiallyMockedFFI();
    }

    public function testFloatFromDecimalComingAsInteger()
    {
        $decimal = 1000;

        $hugeInt = $this->ffi->new(TypeC::DUCKDB_TYPE_HUGEINT->value);
        $hugeInt->lower = 20;
        $hugeInt->upper = 20;

        $this->ffi
            ->expects(self::once())
            ->method('doubleToHugeint')
            ->willReturnCallback(fn () => $hugeInt);

        $this->floatFromDecimalAssertions($decimal, $hugeInt);
    }

    public function testFloatFromDecimalComingAsHugeint()
    {
        $hugeInt = $this->ffi->new(TypeC::DUCKDB_TYPE_HUGEINT->value);
        $hugeInt->lower = 20;
        $hugeInt->upper = 20;

        $this->ffi
            ->expects(self::never())
            ->method('doubleToHugeint');

        $this->floatFromDecimalAssertions($hugeInt, $hugeInt);
    }

    public function floatFromDecimalAssertions(int|NativeCData $decimal, $hugeInt): void
    {
        $logicalType = $this->ffi->new('duckdb_logical_type');

        $expectedFloat = 1.1;

        $decimalWidth = 20;
        $decimalScale = 25;

        $duckDBTypeDecimalFilled = $this->ffi->new(TypeC::DUCKDB_TYPE_DECIMAL->value);
        $duckDBTypeDecimalFilled->width = $decimalWidth;
        $duckDBTypeDecimalFilled->scale = $decimalScale;
        $duckDBTypeDecimalFilled->value = $hugeInt;

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

    public function testLongIntegerToInt(): void
    {
        $longInteger = LongInteger::fromString('104567');
        $this->assertEquals(104567, $longInteger->toInt(MathLib::create()));
    }

    public function testLongIntegerToIntReturnsFalseWhenItIsNotConvertable(): void
    {
        $longInteger = LongInteger::fromString('104567104567104567104567104567104567104567104567104567104567104567104567');
        $this->assertEquals(false, $longInteger->toInt(MathLib::create()));
    }
}
