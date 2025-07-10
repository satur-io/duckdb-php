<?php


require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;

ob_start();
$duckDB = DuckDB::create();
$duckDB->query("PRAGMA enable_profiling = 'no_output';");
$resultAggregate = $duckDB->query('SUMMARIZE TABLE "https://blobs.duckdb.org/data/Star_Trek-Season_1.csv";');
$resultAggregate->print();

$resultTransformation = $duckDB->query("SELECT * FROM repeat('123456789012', 100000);");
$resultTransformation->print();
ob_end_clean();

$resultAggregate->printMetrics();
echo PHP_EOL;
$resultTransformation->printMetrics();
