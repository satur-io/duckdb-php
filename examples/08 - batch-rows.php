<?php


require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;
$duckDB = DuckDB::create();
$result = $duckDB->query('SELECT * FROM repeat("1234567890123456789012345678901234567890", 1000000);');

foreach ($result->vectorChunk() as $rowBatch) {
    $rows = sizeof($rowBatch[0]);

    for ($i = 0; $i < $rows; $i++) {
        foreach ($rowBatch as $columnIndex => $column) {
            printf("%s\n", $column[$i]);
        }
    }
}
