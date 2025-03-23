<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\TypeC;

class DatesAndTimesTest extends TestCase
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        $this->ffi = $this->getMockBuilder(FFIDuckDB::class)
            ->onlyMethods(['fromDate', 'fromTime', 'fromTimeTz', 'fromTimestamp'])
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

    public function testGetTimeFromDuckDBTime(): void
    {
        $time = $this->ffi->new(TypeC::DUCKDB_TYPE_TIME->value);
        $timeStruct = $this->ffi->new('duckdb_time_struct');
        $timeStruct->hour = 12;
        $timeStruct->min = 04;
        $timeStruct->sec = 23;
        $timeStruct->micros = 23002;

        $expectedTime = new Time(12, 04, 23, microseconds: 23002);

        $this->ffi
            ->expects(self::once())
            ->method('fromTime')
            ->with($time)
            ->willReturnCallback(fn () => $timeStruct);

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($expectedTime, $converter->getTimeFromDuckDBTime($time));
    }

    public function testGetTimeFromDuckDBTimeTz(): void
    {
        $time = $this->ffi->new(TypeC::DUCKDB_TYPE_TIME_TZ->value);
        $timeTzStruct = $this->ffi->new('duckdb_time_tz_struct');
        $timeStruct = $this->ffi->new('duckdb_time_struct');
        $timeStruct->hour = 12;
        $timeStruct->min = 04;
        $timeStruct->sec = 23;
        $timeStruct->micros = 23002;
        $timeTzStruct->time = $timeStruct;
        $timeTzStruct->offset = 10;

        $expectedTime = new Time(12, 04, 23, microseconds: 23002, offset: 10, isTimeZoned: true);

        $this->ffi
            ->expects(self::once())
            ->method('fromTimeTz')
            ->with($time)
            ->willReturnCallback(fn () => $timeTzStruct);

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($expectedTime, $converter->getTimeFromDuckDBTimeTz($time));
    }

    public function testGetTimestampFromDuckDBTimestamp(): void
    {
        $converter = new TypeConverter($this->ffi);
        $timestamp = $this->ffi->new('duckdb_timestamp');

        $timestamp->micros = -9223372036854775807;
        self::assertEquals(new Timestamp(infinity: -1), $converter->getTimestampFromDuckDBTimestamp($timestamp));

        $timestamp->micros = 9223372036854775807;
        self::assertEquals(new Timestamp(infinity: 1), $converter->getTimestampFromDuckDBTimestamp($timestamp));

        $timestamp->micros = 922337203685;
        $timestampStruct = $this->ffi->new('duckdb_timestamp_struct');
        $timeStruct = $this->ffi->new('duckdb_time_struct');
        $timeStruct->hour = 12;
        $timeStruct->min = 04;
        $timeStruct->sec = 23;
        $timeStruct->micros = 23002;
        $timestampStruct->time = $timeStruct;
        $dateStruct = $this->ffi->new('duckdb_date_struct');
        $dateStruct->year = 1521;
        $dateStruct->month = 04;
        $dateStruct->day = 23;
        $timestampStruct->date = $dateStruct;

        $this->ffi
            ->expects(self::once())
            ->method('fromTimestamp')
            ->with($timestamp)
            ->willReturnCallback(fn () => $timestampStruct);

        $expectedTimestamp = new Timestamp(
            new Date(1521, 04, 23),
            new Time(12, 04, 23, microseconds: 23002),
        );
        self::assertEquals($expectedTimestamp, $converter->getTimestampFromDuckDBTimestamp($timestamp));
    }
}
