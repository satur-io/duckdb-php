<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use Saturio\DuckDB\Exception\NotSupportedException;

interface FindLibraryInterface
{
    /**
     * @throws NotSupportedException
     */
    public static function headerPath(): string;

    public static function libPath(): string;
}
