<?php

declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\Exception\UnsupportedTypeException;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\Type\Converter\GetDuckDBValue;
use SaturIo\DuckDB\Type\Type;

class GetDuckDBValueTest extends TestCase
{
    private FFIDuckDB $ffi;
    private $testTrait;

    public function setUp(): void
    {
        $this->ffi = $this->createMock(FFIDuckDB::class);
        $this->testTrait = new class {
            use GetDuckDBValue;
        };
    }

    public function testInvalidType()
    {
        $this->expectException(\TypeError::class);
        $this->testTrait->getDuckDBValue(new class {}, $this->ffi);
    }

    public function testUnsupportedType()
    {
        $this->expectException(UnsupportedTypeException::class);
        $this->testTrait->getDuckDBValue('any', $this->ffi, Type::DUCKDB_TYPE_ANY);
    }
}
