#!/usr/bin/env php
<?php
require_once  $_composer_autoload_path ?? __DIR__ . '/vendor/autoload.php';

use Saturio\DuckDB\CLib\Installer;
use Saturio\DuckDB\CLib\Version;
use Saturio\DuckDB\FFI\FindLibrary;

echo "\033[32mDuckDB version\033[0m (\033[36m" . Version::default() . "\033[0m): ";

$version = trim(fgets(STDIN));
$version = empty($version) ? null : $version;

echo "\033[32mInstallation path\033[0m (\033[36m" . FindLibrary::defaultPath($version) . "\033[0m): ";

$path = trim(fgets(STDIN));
$path = empty($path) ? null : $path;

echo "\n\033[36mDownloading and installing DuckDB C library\033[0m\n";
Installer::install($path, $version);
echo "\033[32mC library downloaded and installed\033[0m\n";

$resolvedVersion = Version::resolve($version);
if ($resolvedVersion !== Version::default()) {
    echo "\n\033[31mNow please set the environment variable DUCKDB_PHP_LIB_VERSION={$resolvedVersion}\033[0m\n";
}

if ($path !== null) {
    $fullPath = realpath($path);
    echo "\n\033[31mNow please set the environment variable DUCKDB_PHP_PATH={$fullPath}\033[0m\n\n";
}
