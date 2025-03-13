<?php

declare(strict_types=1);

namespace Unit;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\Type\Converter\GetDuckDBValue;
use Saturio\DuckDB\Type\Type;
use TypeError;

class GetDuckDBValueTest extends TestCase
{
    private $testTrait;

    public function setUp(): void
    {
        $this->testTrait = new class {
            use GetDuckDBValue;
        };
    }

    public function testInvalidType()
    {
        $this->expectException(TypeError::class);
        $this->testTrait->getDuckDBValue(new class {});
    }

    public function testUnsupportedType()
    {
        $this->expectException(UnsupportedTypeException::class);
        $this->testTrait->getDuckDBValue('any', Type::DUCKDB_TYPE_ANY);
    }
}
