<?php

declare(strict_types=1);

namespace Unit\Helper;

use PHPUnit\Framework\MockObject\MockObject;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;

trait PartiallyMockedFFITrait
{
    abstract public function createMock(string $originalClassName): MockObject;

    public function getPartiallyMockedFFI()
    {
        $originalFFI = new FFIDuckDB();
        $ffi = $this->createMock(FFIDuckDB::class);
        $ffi
            ->method('new')
            ->willReturnCallback(fn (...$args) => $originalFFI->new(...$args));
        $ffi
            ->method('addr')
            ->willReturnCallback(fn (...$args) => $originalFFI->addr(...$args));
        $ffi
            ->method('type')
            ->willReturnCallback(fn (...$args) => $originalFFI->type(...$args));

        return $ffi;
    }
}
