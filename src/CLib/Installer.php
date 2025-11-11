<?php

declare(strict_types=1);

namespace Saturio\DuckDB\CLib;

use ReflectionClass;
use Saturio\DuckDB\Exception\CLibInstallationException;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\Exception\NotSupportedException;
use Saturio\DuckDB\FFI\FindLibrary;

class Installer
{
    private const string HEADER_FFI_DEFINITIONS = <<<EOF
#define FFI_SCOPE "DUCKDB"
#define FFI_LIB "%s"

EOF;

    /**
     * @throws NotSupportedException
     * @throws CLibInstallationException
     */
    public static function install(mixed $path = null): void
    {
        try {
            $path = is_string($path) ? $path : null;
            [$headerPath, $libPath] = FindLibrary::headerAndLibrary();
            echo sprintf('Header found: %s'.PHP_EOL, $headerPath);
            echo sprintf('Library found: %s'.PHP_EOL, $libPath);
            echo PHP_EOL;
            echo '✔ DuckDB C library is already installed.'.PHP_EOL;

            return;
        } catch (MissedLibraryException) {
            echo 'DuckDB C library not found. Starting installation'.PHP_EOL;
        }

        $path = $path ?? FindLibrary::defaultPath();
        if (!defined('DUCKDB_PHP_LIB_VERSION')) {
            require_once __DIR__.'/../../config.php';
        }
        Downloader::download($path, DUCKDB_PHP_LIB_VERSION);
        self::copyHeader($path);
    }

    /**
     * @throws NotSupportedException
     * @throws CLibInstallationException
     */
    private static function copyHeader(string $path): void
    {
        $platformInfo = PlatformInfo::getPlatformInfo();

        $headerPath = implode(DIRECTORY_SEPARATOR, [$path, 'duckdb-ffi.h']);
        $libraryPath = implode(DIRECTORY_SEPARATOR, [$path, $platformInfo['file']]);

        $originalHeaderFile = implode(
            DIRECTORY_SEPARATOR,
            [
                dirname((new ReflectionClass(self::class))->getFileName()),
                '..', '..', 'header',
                $platformInfo['platform'],
                'duckdb-ffi.h',
            ]);

        if (!file_exists($originalHeaderFile)) {
            throw new CLibInstallationException(sprintf('Couldn\'t find original header file "%s".', $originalHeaderFile));
        }

        file_put_contents(
            $headerPath,
            sprintf(self::HEADER_FFI_DEFINITIONS, realpath($libraryPath)).file_get_contents($originalHeaderFile)
        );
    }
}
