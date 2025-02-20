<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use Saturio\DuckDB\Exception\NotSupportedException;

class FindLibrary
{
    /**
     * @throws NotSupportedException
     */
    public static function headerPath(): string
    {
        return implode('/', [self::path(), 'duckdb-ffi.h']);
    }

    public static function libPath(): string
    {
        $os = php_uname('s');

        return match ($os) {
            'Windows NT' => implode(DIRECTORY_SEPARATOR, [self::path(), 'duckdb.dll']),
            'Linux' => implode(DIRECTORY_SEPARATOR, [self::path(), 'libduckdb.so']),
            'Darwin' => implode(DIRECTORY_SEPARATOR, [self::path(), 'libduckdb.dylib']),
        };
    }

    /**
     * @throws NotSupportedException
     */
    private static function path(): string
    {
        $os = php_uname('s');
        $machine = php_uname('m');

        $machine = ('Linux' === $os && 'x86_64' === $machine) ? 'amd64' : $machine;
        $machine = ('Windows NT' === $os && 'AMD64' === $machine) ? 'amd64' : $machine;

        $thisClassReflection = new \ReflectionClass(self::class);
        $path = dirname($thisClassReflection->getFileName());

        return match ($os) {
            'Windows NT' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', 'lib', "windows-{$machine}"]),
            'Linux' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', 'lib', "linux-{$machine}"]),
            'Darwin' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', 'lib', 'osx-universal']),
            default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
        };
    }
}
