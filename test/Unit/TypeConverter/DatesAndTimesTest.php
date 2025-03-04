<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\TypeC;

class DatesAndTimesTest extends TestCase
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        $this->ffi = $this->getMockBuilder(FFIDuckDB::class)
            ->onlyMethods(['fromDate'])
            ->getMock();
        $this->createMock(FFIDuckDB::class);
    }

    public function testDateFromDuckDBDate(): void
    {
        $date = $this->ffi->new(TypeC::DUCKDB_TYPE_DATE->value);
        $dateStruct = $this->ffi->new('duckdb_date_struct');
        $dateStruct->year = 1521;
        $dateStruct->month = 04;
        $dateStruct->day = 23;

        $expectedDate = new Date(1521, 04, 23);

        $this->ffi
            ->expects(self::once())
            ->method('fromDate')
            ->with($date)
            ->willReturnCallback(fn () => $dateStruct);

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($expectedDate, $converter->getDateFromDuckDBDate($date));
    }
}
