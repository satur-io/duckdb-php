<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\TypeC;
use Unit\Helper\PartiallyMockedFFITrait;

class VarCharConverterTest extends TestCase
{
    use PartiallyMockedFFITrait;
    private FFIDuckDB $ffi;
    public string $expectedString;

    public function setUp(): void
    {
        $this->ffi = new FFIDuckDB();
    }

    // This happens when data struct length is <= 12
    public function testInlinedVarchar()
    {
        $expectedString = str_split('Sort string');
        $charArrayType = FFI::arrayType($this->ffi->type('char'), [12]);
        $charArray = $this->ffi->new($charArrayType);
        foreach ($expectedString as $key => $char) {
            $charArray[$key] = $char;
        }
        $inlinedCDataVarChar = $this->ffi->new(TypeC::DUCKDB_TYPE_VARCHAR->value);
        $inlinedCDataVarChar->value->inlined->length = count($expectedString);
        $inlinedCDataVarChar->value->inlined->inlined = $charArray;
        $inlinedCDataVarChar->value->pointer->length = count($expectedString);

        $converter = new TypeConverter($this->ffi);
        self::assertEquals(implode($expectedString), $converter->getVarChar($inlinedCDataVarChar));
    }

    // This happens when data struct length is > 12
    public function testPointerVarchar()
    {
        $expectedString = str_split('More than 12 characters string');
        $charArrayType = FFI::arrayType($this->ffi->type('char'), [count($expectedString)]);
        $charArray = $this->ffi->new($charArrayType);
        foreach ($expectedString as $key => $char) {
            $charArray[$key] = $char;
        }
        $pointerToCharArray = $this->ffi->cast('char *', $charArray);
        $inlinedCDataVarChar = $this->ffi->new(TypeC::DUCKDB_TYPE_VARCHAR->value);
        $inlinedCDataVarChar->value->inlined->length = count($expectedString);
        $inlinedCDataVarChar->value->pointer->length = count($expectedString);
        $inlinedCDataVarChar->value->pointer->ptr = $pointerToCharArray;

        $converter = new TypeConverter($this->ffi);
        self::assertEquals(implode($expectedString), $converter->getVarChar($inlinedCDataVarChar));
    }
}
