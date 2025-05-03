<?php


require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;
$duckDB = DuckDB::create();
$result = $duckDB->query('CREATE TABLE people (id INTEGER, name VARCHAR);');

$appender = $duckDB->appender('people');

for ($i = 0; $i < 100; ++$i) {
    $appender->append(rand(1, 100000));
    $appender->append('string-'.rand(1, 100));
    $appender->endRow();
}

$appender->flush();
$total = $duckDB->query('SELECT * FROM people LIMIT 10')->print();
