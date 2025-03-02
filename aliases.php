<?php

if (!getenv('DUCKDB_PHP_LIB_TEST') && !str_ends_with($_SERVER['argv'][0] ?? '', 'phpunit')) {
    include_once __DIR__ . '/aliases-prod.php';
}
