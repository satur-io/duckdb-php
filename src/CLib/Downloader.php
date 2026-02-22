<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use PharData;
use Saturio\DuckDB\Exception\CLibInstallationException;
use Saturio\DuckDB\Exception\NotSupportedException;

class Downloader
{
    private const string LIB_URL = 'https://github.com/duckdb/duckdb/releases/download/v%s/libduckdb-%s.zip';

    /**
     * @throws NotSupportedException
     * @throws CLibInstallationException
     */
    public static function download(string $path, ?string $version = null): void
    {
        $version = Version::resolve($version);
        $platformInfo = PlatformInfo::getPlatformInfo();
        $zipFile = 'lib.zip';

        file_put_contents($zipFile,
            file_get_contents(sprintf(
                self::LIB_URL,
                $version,
                $platformInfo['platform'],
            ))
        );

        $checksum = Version::checksumFor($version, $platformInfo['platform']);
        if ($checksum !== null) {
            if ($checksum !== hash('sha256', file_get_contents($zipFile))) {
                throw new CLibInstallationException('Bad checksum');
            }
        } else {
            echo sprintf('Warning: checksum not available for DuckDB v%s (%s). Skipping verification.'.PHP_EOL, $version, $platformInfo['platform']);
        }

        $phar = new PharData($zipFile);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if ($phar->extractTo($path, [$platformInfo['file']], true)) {
            echo 'C lib downloaded'.PHP_EOL;
        } else {
            echo sprintf('ERROR: Couldn\'t extract %s from ZIP file.', $platformInfo['file']);
        }

        echo "Removing {$zipFile}...\n";
        unlink($zipFile);

        echo "DuckDB C lib downloaded!\n";
    }
}
