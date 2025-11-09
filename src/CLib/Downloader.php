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
    public static function download(string $path, string $version): void
    {
        $platformInfo = PlatformInfo::getPlatformInfo();
        $zipFile = 'lib.zip';

        file_put_contents($zipFile,
            file_get_contents(sprintf(
                self::LIB_URL,
                $version,
                $platformInfo['platform'],
            ))
        );

        if (DUCKDB_PHP_LIB_CHECKSUMS[$platformInfo['platform']] !== hash('sha256', file_get_contents($zipFile))) {
            throw new CLibInstallationException('Bad checksum');
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
