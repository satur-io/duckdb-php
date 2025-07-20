<?php
$command = 'composer require satur.io/duckdb ';
$command .= 'satur.io/duckdb-clib-';
$command .= match (php_uname('s')) {
    'Windows NT' => match (php_uname('m')) {
        'AMD64', 'x64' => 'windows-amd64',
        'ARM64' => 'windows-arm64',
    },
    'Linux' => match (php_uname('m')) {
        'x86_64' => 'linux-amd64',
        'aarch64' => 'linux-arm64',
    },
    'Darwin' => 'osx-universal',
};

echo $command;
