<?php

require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;

$duckDB = DuckDB::create();

$result = $duckDB->query( "SELECT * FROM (VALUES ('quack', 'queck'), ('quick', NULL), ('duck', 'cool'));");

foreach ($result->columnNames() as $columnName) {
    echo $columnName . "\t";
}
foreach ($result->rows() as $row) {
    echo "\n";
    foreach ($row as $column => $value) {
        echo $value . "\t";
    }
}
