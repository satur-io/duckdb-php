#!/usr/bin/env php
<?php
require_once  $_composer_autoload_path ?? __DIR__ . '/vendor/autoload.php';

use Composer\InstalledVersions;
use Saturio\DuckDB\CLib\Downloader;

$version = InstalledVersions::getPrettyVersion('satur.io/duckdb');
$path = 'lib';

echo "\033[32mInstallation path\033[0m (\033[36m" . $path . "\033[0m): ";

$user_input = trim(fgets(STDIN));

if (!empty($user_input)) {
    $path = $user_input;
}

echo "\n\033[36mDownloading DuckDB C library in {$path}\033[0m\n";
Downloader::download($path, $version);
$fullPath = realpath($path);
echo "\033[32mC library downloaded\033[0m\n";
echo "\n\033[31mNow please set the environment variable DUCKDB_PHP_PATH={$fullPath}\033[0m\n\n";
