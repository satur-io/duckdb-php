<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use ReflectionClass;
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
            default => throw new NotSupportedException("Unsupported OS: {$os}"),
        };
    }

    /**
     * @throws NotSupportedException
     */
    private static function path(): string
    {
        $os = php_uname('s');
        $machine = php_uname('m');

        $libDirectory = getenv('DUCKDB_PHP_LIB_DIRECTORY') ? getenv('DUCKDB_PHP_LIB_DIRECTORY') : 'lib';

        if ('Windows NT' === $os) {
            $machine = match ($machine) {
                'AMD64', 'x64' => 'amd64',
                'ARM64' => 'arm64',
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            };
        }

        if ('Linux' === $os) {
            $machine = match ($machine) {
                'x86_64' => 'amd64',
                'aarch64' => 'arm64',
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            };
        }

        $thisClassReflection = new ReflectionClass(self::class);
        $path = dirname($thisClassReflection->getFileName());

        return match ($os) {
            'Windows NT' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', $libDirectory, "windows-{$machine}"]),
            'Linux' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', $libDirectory, "linux-{$machine}"]),
            'Darwin' => implode(DIRECTORY_SEPARATOR, [$path, '..', '..', $libDirectory, 'osx-universal']),
            default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
        };
    }
}
