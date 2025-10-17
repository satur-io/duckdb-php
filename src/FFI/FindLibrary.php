<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use ReflectionClass;
use Saturio\DuckDB\CLib\PlatformInfo;
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

    /**
     * @throws NotSupportedException
     */
    public static function libPath(): string
    {
        $file = PlatformInfo::getPlatformInfo()['file'];
        return implode(DIRECTORY_SEPARATOR, [self::path(), $file]);
    }

    /**
     * @throws NotSupportedException
     */
    private static function path(): string
    {
        $thisClassReflection = new ReflectionClass(self::class);
        $defaultPath = implode(DIRECTORY_SEPARATOR, [dirname($thisClassReflection->getFileName()), '..', '..', 'lib']);

        $libDirectory = self::getConfigValue('DUCKDB_PHP_PATH', $defaultPath);

        $platform = PlatformInfo::getPlatformInfo()['platform'];

        return implode(DIRECTORY_SEPARATOR, [$libDirectory, $platform]);
    }

    private static function getConfigValue(string $key, ?string $default = null): ?string
    {
        return getenv($key) ?: (defined($key) ? constant($key) : $default);
    }
}