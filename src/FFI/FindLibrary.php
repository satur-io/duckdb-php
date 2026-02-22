<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use ReflectionClass;
use Saturio\DuckDB\CLib\Version;
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
    public static function headerAndLibrary(?string $version = null): array
    {
        $configuredPath = self::getConfiguredDuckdbPath();
        $headerPath = FindLibrary::headerPath($version);

        if (!file_exists($headerPath)) {
            if ($configuredPath !== null) {
                throw new MissedLibraryException("Could not load library header file '$headerPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
            }

            $legacyHeaderPath = FindLibrary::headerPathLegacy();
            if (!file_exists($legacyHeaderPath)) {
                throw new MissedLibraryException("Could not load library header file '$headerPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
            }
            $headerPath = $legacyHeaderPath;
        }

        $libPath = FindLibrary::libPath($version);

        if (!file_exists($libPath)) {
            if ($configuredPath !== null) {
                throw new MissedLibraryException("Could not load library '$libPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
            }

            $legacyLibPath = FindLibrary::libPathLegacy();
            if (!file_exists($legacyLibPath)) {
                throw new MissedLibraryException("Could not load library '$libPath'. Check documentation for installation options:  https://duckdb-php.readthedocs.io.");
            }
            $libPath = $legacyLibPath;
        }

        return [$headerPath, $libPath];
    }

    public static function defaultPath(?string $version = null): string
    {
        $rootInstallationPath = realpath(
            implode(DIRECTORY_SEPARATOR,
                [dirname((new ReflectionClass(self::class))->getFileName()), '..', '..']
            )
        );

        $basePath = implode(DIRECTORY_SEPARATOR, [$rootInstallationPath, 'lib']);
        $resolvedVersion = Version::resolve($version);

        return implode(DIRECTORY_SEPARATOR, [$basePath, $resolvedVersion]);
    }

    private static function headerPath(?string $version = null): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::path($version), 'duckdb-ffi.h']);
    }

    /**
     * @throws NotSupportedException
     */
    private static function libPath(?string $version = null): string
    {
        $file = PlatformInfo::getPlatformInfo()['file'];

        return implode(DIRECTORY_SEPARATOR, [self::path($version), $file]);
    }

    private static function path(?string $version = null): string
    {
        return
            self::getConfiguredDuckdbPath()
            ?? self::defaultPath($version);
    }

    private static function headerPathLegacy(): string
    {
        return implode(DIRECTORY_SEPARATOR, [self::legacyPath(), 'duckdb-ffi.h']);
    }

    /**
     * @throws NotSupportedException
     */
    private static function libPathLegacy(): string
    {
        $file = PlatformInfo::getPlatformInfo()['file'];

        return implode(DIRECTORY_SEPARATOR, [self::legacyPath(), $file]);
    }

    private static function legacyPath(): string
    {
        $rootInstallationPath = realpath(
            implode(DIRECTORY_SEPARATOR,
                [dirname((new ReflectionClass(self::class))->getFileName()), '..', '..']
            )
        );

        return implode(DIRECTORY_SEPARATOR, [$rootInstallationPath, 'lib']);
    }

    private static function getConfiguredDuckdbPath(): ?string
    {
        $phpConstantValueOrNull = fn () => defined(self::KEY) ? constant(self::KEY) : null;

        return getenv(self::KEY) ?: $phpConstantValueOrNull();
    }
}
