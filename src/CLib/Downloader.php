<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use PharData;
use Saturio\DuckDB\Exception\CLibInstallationException;
use Saturio\DuckDB\Exception\NotSupportedException;

class Downloader
{
    private const string LIB_URL = 'https://github.com/duckdb/duckdb/releases/download/v%s/duckdb-shared-libs-%s.tar.gz';

    /**
     * @throws NotSupportedException
     * @throws CLibInstallationException
     */
    public static function download(string $path, string $version): void
    {
        $platformInfo = PlatformInfo::getPlatformInfo();
        $archive = 'lib.tar.gz';

        file_put_contents($archive,
            file_get_contents(sprintf(
                self::LIB_URL,
                $version,
                $platformInfo['platform'],
            ))
        );

        if (DUCKDB_PHP_LIB_CHECKSUMS[$platformInfo['platform']] !== hash('sha256', file_get_contents($archive))) {
            throw new CLibInstallationException('Bad checksum');
        }

        $phar = new PharData($archive);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if ($phar->extractTo($path, [$platformInfo['file']], true)) {
            echo 'C lib downloaded'.PHP_EOL;
        } else {
            echo sprintf('ERROR: Couldn\'t extract %s from tar.gz file.', $platformInfo['file']);
        }

        echo "Removing {$archive}...\n";
        unlink($archive);

        echo "DuckDB C lib downloaded!\n";
    }
}
