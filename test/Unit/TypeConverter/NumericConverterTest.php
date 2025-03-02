<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\NumericConverter;
use Unit\Abstract\TestWithInterfaces;
use Unit\Helper\DummyCData;

#[RunClassInSeparateProcess]
class NumericConverterTest extends TestWithInterfaces
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        parent::setUp();
        $this->ffi = $this->createMock(FFIDuckDB::class);
    }

    public function testFloatFromDecimalComingAsInteger()
    {
        $decimal = 1000;

        $hugeInt = new class('This is a Hugeint') extends DummyCData {
            public function __construct(public $content)
            {
            }
        };
        $this->ffi
            ->expects(self::once())
            ->method('doubleToHugeint')
            ->willReturn($hugeInt);

        $this->floatFromDecimalAssertions($decimal, $hugeInt);
    }

    public function testFloatFromDecimalComingAsHugeint()
    {
        $data = new class('This is a Hugeint') extends DummyCData {
            public function __construct(public $content)
            {
            }
        };

        $this->ffi
            ->expects(self::never())
            ->method('doubleToHugeint');

        $this->floatFromDecimalAssertions($data, $data);
    }

    public function floatFromDecimalAssertions(int|CDataInterface $decimal, $hugeInt): void
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
        $duckDBTypeDecimalFilled->value = $hugeInt;

        $this->ffi
            ->expects(self::any())
            ->method('new')
            ->with('duckdb_decimal', false)
            ->willReturn($duckDBTypeDecimal);

        $this->ffi
            ->expects(self::any())
            ->method('addr')
            ->willReturn(new class extends DummyCData {});

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
