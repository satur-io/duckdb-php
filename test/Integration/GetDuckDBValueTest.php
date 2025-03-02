<?php

declare(strict_types=1);

namespace Integration;

use Integration\Helper\IntegrationTestTrait;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Type;

class GetDuckDBValueTest extends TestCase
{
    use IntegrationTestTrait;
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
