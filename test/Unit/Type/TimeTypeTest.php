<?php

declare(strict_types=1);

namespace Unit\Type;

use DateTime;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\TimePrecision;
use Saturio\DuckDB\Type\Timestamp;

class TimeTypeTest extends TestCase
{
    #[DataProvider('timesProvider')]
    public function testToString(Time $time, string $expectedString): void
    {
        $this->assertEquals($expectedString, (string) $time);
    }

    public function testInvalidTime(): void
    {
        $this->expectException(InvalidTimeException::class);
        new Time(12, 3, 4, 1, 2);
    }

    public function testTimestampFromDateTime(): void
    {
        $dateTime = new DateTime('1521-04-23 12:00:00.12345');

        $timestamp = Timestamp::fromDatetime($dateTime);
        $this->assertEquals($dateTime, $timestamp->toDateTime());
    }

    public function testTimestampInvalidTime(): void
    {
        $this->expectException(InvalidTimeException::class);
        $dateTime = new DateTime('1521-04-23 12:00:00.12345');

        Timestamp::fromDatetime($dateTime, TimePrecision::SECONDS, nanoseconds: 123);
    }

    public function testTimestampFromDateTimeWithNanoseconds(): void
    {
        $dateTime = new DateTime('1521-04-23 12:00:00');

        $timestamp = Timestamp::fromDatetime($dateTime, TimePrecision::NANOSECONDS, nanoseconds: 123456789);
        $this->assertEquals($dateTime->modify('+123456 microseconds'), $timestamp->toDateTime());
    }

    public function testTimestampFromDateTimeSecondsPrecision(): void
    {
        $dateTime = new DateTime('1521-04-23 12:00:00.123456789');

        $timestamp = Timestamp::fromDatetime($dateTime, TimePrecision::SECONDS);
        $this->assertEquals($dateTime->modify('-123456 microseconds'), $timestamp->toDateTime());
    }

    public function testTimestampInfinityToString(): void
    {
        $timestamp = new Timestamp(infinity: 1);
        $this->assertEquals('+infinity', (string) $timestamp);

        $timestamp = new Timestamp(infinity: -1);
        $this->assertEquals('-infinity', (string) $timestamp);
    }

    public static function timesProvider(): array
    {
        return [
            [new Time(10, 5, 2), '10:05:02.0'],
            [new Time(10, 5, 2, 1), '10:05:02.1000000'],
            [new Time(10, 5, 2, microseconds: 1), '10:05:02.1000'],
            [new Time(10, 5, 2, nanoseconds: 1), '10:05:02.1'],
        ];
    }
}
