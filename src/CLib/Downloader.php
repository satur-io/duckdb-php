<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use Saturio\DuckDB\Exception\NotSupportedException;
use ZipArchive;

class Downloader
{
    private const string LIB_URL = 'https://github.com/satur-io/duckdb-php/releases/download/%s/%s.zip';

    /**
     * @throws NotSupportedException
     */
    public static function download(string $path, string $version): void
    {
        $platform = PlatformInfo::getPlatformInfo()['platform'];
        $zipFile = 'lib.zip';

        file_put_contents($zipFile,
            file_get_contents(sprintf(
                self::LIB_URL,
                $version,
                $platform,
            ))
        );

        $zip = new ZipArchive();
        if (true === $zip->open($zipFile)) {
            $zip->extractTo($path.DIRECTORY_SEPARATOR.$platform);
            $zip->close();
        } else {
            exit("Unzip failed.\n");
        }
        echo "Removing {$zipFile}...\n";
        unlink($zipFile);

        echo "DuckDB C lib downloaded!\n";
    }
}
