<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Unit\Helper\PartiallyMockedFFITrait;

class BigIntConverterTest extends TestCase
{
    use PartiallyMockedFFITrait;

    private TypeConverter $converterWithMath;
    private TypeConverter $converterWithoutMath;

    protected function setUp(): void
    {
        $ffi = $this->getPartiallyMockedFFI();
        $this->converterWithMath = new TypeConverter($ffi, MathLib::create());
        $this->converterWithoutMath = new TypeConverter($ffi, null);
    }

    public function testSignedBigIntWithoutMathLib(): void
    {
        // Signed BIGINT should work without bcmath since values fit in PHP_INT_MAX
        $result = $this->converterWithoutMath->getBigIntFromDuckDBBigInt(12345, unsigned: false);
        self::assertSame(12345, $result);
    }

    public function testSignedBigIntNegativeWithoutMathLib(): void
    {
        // Negative signed BIGINT should also work without bcmath
        $result = $this->converterWithoutMath->getBigIntFromDuckDBBigInt(-12345, unsigned: false);
        self::assertSame(-12345, $result);
    }

    public function testUnsignedBigIntSmallValueWithoutMathLib(): void
    {
        // Unsigned BIGINT with value < 2^63 (appears non-negative) should work without bcmath
          $result = $this->converterWithoutMath->getBigIntFromDuckDBBigInt(12345, unsigned: true);
          self::assertSame(12345, $result);
      }

    public function testUnsignedBigIntLargeValueWithoutMathLibThrows(): void
    {
        // Unsigned BIGINT with value >= 2^63 (appears negative due to overflow) requires bcmath
          // This is the ONLY case that should throw
          $this->expectException(BigNumbersNotSupportedException::class);
          $this->converterWithoutMath->getBigIntFromDuckDBBigInt(-1, unsigned: true);
      }

    public function testUnsignedBigIntLargeValueWithMathLib(): void
    {
        // With bcmath available, large unsigned values should work
        $result = $this->converterWithMath->getBigIntFromDuckDBBigInt(-1, unsigned: true);
        // -1 as unsigned 64-bit = 2^64 - 1 = 18446744073709551615
        self::assertSame('18446744073709551615', (string) $result);
    }
}