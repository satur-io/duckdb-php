<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Date;
use Unit\Abstract\TestWithInterfaces;
use Unit\Helper\DummyCData;

#[RunClassInSeparateProcess]
class DatesAndTimesTest extends TestWithInterfaces
{
    private FFIDuckDB $ffi;

    public function setUp(): void
    {
        parent::setUp();
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

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($expectedDate, $converter->getDateFromDuckDBDate($date));
    }
}
