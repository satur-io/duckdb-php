<?php

declare(strict_types=1);

namespace Unit\Abstract;

use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\CDataInterface;

#[RunClassInSeparateProcess]
abstract class TestWithInterfaces extends TestCase
{
    public function setUp(): void
    {
        if (class_exists('\Saturio\DuckDB\Native\FFI\CData')) {
            return;
        }

        class_alias(CDataInterface::class, '\Saturio\DuckDB\Native\FFI\CData');
    }
}
