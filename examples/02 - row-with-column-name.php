<?php

require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;

$duckDB = DuckDB::create();

$result = $duckDB->query( "SELECT 'quack' as column1, 'queck' as column2, 'quick' as column3;");

foreach ($result->rows(columnNameAsKey: true) as $row) {
    foreach ($row as $column => $value) {
        echo "{$column}: {$value}" . "\n";
    }
}
