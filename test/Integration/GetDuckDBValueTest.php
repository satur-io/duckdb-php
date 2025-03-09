<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Type;

class GetDuckDBValueTest extends TestCase
{
    private FFIDuckDB $ffi;
    private TypeConverter $converter;

    protected function setUp(): void
    {
        $this->ffi = new FFIDuckDB();
        $this->converter = new TypeConverter($this->ffi);
    }

    public function testInferredBool()
    {
        $duckDBValue = $this->converter->getDuckDBValue(true);
        self::assertEquals(
            Type::DUCKDB_TYPE_BOOLEAN,
            Type::from($this->ffi->getTypeId($this->ffi->getValueType($duckDBValue)))
        );
    }

    public function testInferredInt()
    {
        $duckDBValue = $this->converter->getDuckDBValue(12);
        self::assertEquals(
            Type::DUCKDB_TYPE_INTEGER,
            Type::from($this->ffi->getTypeId($this->ffi->getValueType($duckDBValue)))
        );
    }
}
