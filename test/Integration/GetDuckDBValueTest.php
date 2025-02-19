<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Converter\TypeConverter;
use SaturIo\DuckDB\Type\Type;

class GetDuckDBValueTest extends TestCase
{
    private FFIDuckDB $ffi;

    protected function setUp(): void
    {
        $this->ffi = new FFIDuckDB();
    }

    public function testInferredBool()
    {
        $duckDBValue = TypeConverter::getDuckDBValue(true, $this->ffi);
        self::assertEquals(
            Type::DUCKDB_TYPE_BOOLEAN,
            Type::from($this->ffi->getTypeId($this->ffi->getValueType($duckDBValue)))
        );
    }

    public function testInferredInt()
    {
        $duckDBValue = TypeConverter::getDuckDBValue(12, $this->ffi);
        self::assertEquals(
            Type::DUCKDB_TYPE_INTEGER,
            Type::from($this->ffi->getTypeId($this->ffi->getValueType($duckDBValue)))
        );
    }
}
