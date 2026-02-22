<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use Saturio\DuckDB\Exception\NotSupportedException;

final class Version
{
    public const string ENV_KEY = 'DUCKDB_PHP_LIB_VERSION';

    /**
     * @throws NotSupportedException
     */
    public static function resolve(?string $version = null): string
    {
        $version = self::normalize($version) ?? self::normalize(self::fromEnv()) ?? self::normalize(self::default());

        if ($version === null) {
            throw new NotSupportedException('DuckDB library version is not configured.');
        }

        self::assertSupported($version);

        return $version;
    }

    public static function default(): string
    {
        if (defined('DUCKDB_PHP_LIB_DEFAULT_VERSION')) {
            return DUCKDB_PHP_LIB_DEFAULT_VERSION;
        }

        if (defined('DUCKDB_PHP_LIB_VERSION')) {
            return DUCKDB_PHP_LIB_VERSION;
        }

        return '';
    }

    public static function supported(): array
    {
        if (defined('DUCKDB_PHP_SUPPORTED_VERSIONS')) {
            return DUCKDB_PHP_SUPPORTED_VERSIONS;
        }

        $default = self::default();

        return $default === '' ? [] : [$default];
    }

    public static function checksumFor(string $version, string $platform): ?string
    {
        if (!defined('DUCKDB_PHP_LIB_CHECKSUMS')) {
            return null;
        }

        $checksums = DUCKDB_PHP_LIB_CHECKSUMS;

        if (!isset($checksums[$version])) {
            return null;
        }

        return $checksums[$version][$platform] ?? null;
    }

    public static function normalize(?string $version): ?string
    {
        if ($version === null) {
            return null;
        }

        $version = trim($version);
        if ($version === '') {
            return null;
        }

        if ($version[0] === 'v' || $version[0] === 'V') {
            $version = substr($version, 1);
        }

        return $version;
    }

    /**
     * @throws NotSupportedException
     */
    private static function assertSupported(string $version): void
    {
        $supported = self::supported();
        if ($supported !== [] && !in_array($version, $supported, true)) {
            throw new NotSupportedException(sprintf(
                'DuckDB library version "%s" is not supported. Supported versions: %s',
                $version,
                implode(', ', $supported)
            ));
        }
    }

    private static function fromEnv(): ?string
    {
        $env = getenv(self::ENV_KEY);

        return is_string($env) && $env !== '' ? $env : null;
    }
}
