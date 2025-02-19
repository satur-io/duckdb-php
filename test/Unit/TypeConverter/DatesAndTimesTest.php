<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Converter\TypeConverter;
use SaturIo\DuckDB\Type\Date;
use Unit\Helper\DummyCData;

class DatesAndTimesTest extends TestCase
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        $this->ffi = $this->createMock(FFIDuckDB::class);
    }

    public function testDateFromDuckDBDate(): void
    {
        $date = new DummyCData();
        $dateStruct = new class extends DummyCData {
            public $year = 1521;
            public $month = 04;
            public $day = 23;
        };
        $expectedDate = new Date(1521, 04, 23);

        $this->ffi
            ->expects(self::once())
            ->method('fromDate')
            ->with($date)
            ->willReturn($dateStruct);

        self::assertEquals($expectedDate, TypeConverter::getDateFromDuckDBDate($date, $this->ffi));
    }
}
