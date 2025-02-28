<?php

namespace Unit\Type;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\Type\Time;

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
        new Time(12,3,4,1,2);
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