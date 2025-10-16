<?php

namespace Saturio\DuckDB\CLib;

use Exception;
use Saturio\DuckDB\Exception\NotSupportedException;

class Downloader
{
    private const string LIB_URL = 'https://github.com/satur-io/duckdb-php/releases/download/%s/%s.zip';

    /**
     * @throws Exception
     */
    public static function download(string $path, string $version): void
    {
        $os = php_uname('s');
        $machine = php_uname('m');

        $platform = match ($os) {
            'Windows NT' => match ($machine) {
                'AMD64', 'x64' => 'windows-amd64',
                'ARM64' => 'windows-arm64',
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            },
            'Linux' => match ($machine) {
                'x86_64' => 'linux-amd64',
                'aarch64' => 'linux-arm64',
                default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
            },
            'Darwin' => 'osx-universal',
            default => throw new NotSupportedException("Unsupported OS: {$os}-{$machine}"),
        };
        $zipFile = 'lib.zip';

        file_put_contents($zipFile,
            file_get_contents(sprintf(
                self::LIB_URL,
                $version,
                $platform,
            ))
        );

        $zip = new \ZipArchive;
        if ($zip->open($zipFile) === TRUE) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            die("Unzip failed.\n");
        }
        echo "Removing {$zipFile}...\n";
        unlink($zipFile);

        echo "DuckDB C lib downloaded!\n";
    }
}
