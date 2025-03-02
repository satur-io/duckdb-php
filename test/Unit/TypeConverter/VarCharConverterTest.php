<?php

declare(strict_types=1);

namespace Unit\TypeConverter;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Unit\Abstract\TestWithInterfaces;
use Unit\Helper\DummyCData;

#[RunClassInSeparateProcess]
class VarCharConverterTest extends TestWithInterfaces
{
    private FFIDuckDB $ffi;
    public string $expectedString;

    public function setUp(): void
    {
        parent::setUp();
        $this->ffi = $this->createMock(FFIDuckDB::class);

        $ffiClass = new class($this) extends TypeConverter {
            private static $test;

            public function __construct($test)
            {
                self::$test = $test;
            }

            public static function string($data, $length)
            {
                self::$test->assertEquals($length, strlen($data));

                return self::$test->expectedString;
            }
        };

        if (!class_exists('\Saturio\DuckDB\Native\FFI')) {
            class_alias($ffiClass::class, '\Saturio\DuckDB\Native\FFI');
        }
    }

    // This happens when data struct length is <= 12
    public function testInlinedVarchar()
    {
        $this->expectedString = 'Sort string';

        $inlinedCDataVarChar = $this->toStdClass(
            [
                'inlined' => [
                    'length' => 11,
                    'inlined' => $this->expectedString,
                ],
                'pointer' => [
                    'length' => 11,
                    'ptr' => 'Sort string-------',
                ],
            ]);

        $inlinedVarChar = new class($inlinedCDataVarChar) extends DummyCData {
            public function __construct(public $value)
            {
            }
        };

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($this->expectedString, $converter->getVarChar($inlinedVarChar));
    }

    // This happens when data struct length is > 12
    public function testPointerVarchar()
    {
        $this->expectedString = 'The expected string';
        $pointerCDataVarChar = $this->toStdClass(
            [
                'inlined' => [
                    'length' => 19,
                    'inlined' => substr($this->expectedString, 0, 12),
                ],
                'pointer' => [
                    'length' => 19,
                    'ptr' => $this->expectedString,
                ],
            ]);

        $inlinedVarChar = new class($pointerCDataVarChar) extends DummyCData {
            public function __construct(public $value)
            {
            }
        };

        $converter = new TypeConverter($this->ffi);
        self::assertEquals($this->expectedString, $converter->getVarChar($inlinedVarChar));
    }

    public function toStdClass(array $array): \stdClass
    {
        return json_decode(json_encode($array));
    }
}
