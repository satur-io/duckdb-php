<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

interface FFIInterface
{
    public static function string($data, ?int $length = null): string;
}
