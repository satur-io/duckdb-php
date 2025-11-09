<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use ReflectionClass;
use Saturio\DuckDB\CLib\PlatformInfo;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\Exception\NotSupportedException;

class FindLibrary
{
    private const string KEY = 'DUCKDB_PHP_PATH';

    /**
     * @throws NotSupportedException
     * @throws MissedLibraryException
     */
    public static function headerAndLibrary(): array
    {
        $headerPath = FindLibrary::headerPath();

        if (!file_exists($headerPath)) {
            throw new MissedLibraryException("Could not load library header file '$headerPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
        }

        $libPath = FindLibrary::libPath();

        if (!file_exists($libPath)) {
            throw new MissedLibraryException("Could not load library '$libPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
        }

        return [$headerPath, $libPath];
    }

    public static function defaultPath(): string
    {
        $rootInstallationPath = realpath(
            implode(DIRECTORY_SEPARATOR,
                [dirname((new ReflectionClass(self::class))->getFileName()), '..', '..']
            )
        );

        return implode(DIRECTORY_SEPARATOR, [$rootInstallationPath, 'lib']);
    }

    private static function headerPath(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::path(), 'duckdb-ffi.h']);
    }

    /**
     * @throws NotSupportedException
     */
    private static function libPath(): string
    {
        $file = PlatformInfo::getPlatformInfo()['file'];

        return implode(DIRECTORY_SEPARATOR, [self::path(), $file]);
    }

    private static function path(): string
    {
        return
            self::getConfiguredDuckdbPath()
            ?? self::defaultPath();
    }

    private static function getConfiguredDuckdbPath(): ?string
    {
        $phpConstantValueOrNull = fn () => defined(self::KEY) ? constant(self::KEY) : null;

        return getenv(self::KEY) ?: $phpConstantValueOrNull();
    }
}
