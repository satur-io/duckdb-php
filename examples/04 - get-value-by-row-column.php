<?php
/**
 *
 * This is just an example to show how you can go into low level
 * handling directly datachunks and vectors from the result.
 *
 * DuckDB C API docs can be useful to understand those concepts
 * https://duckdb.org/docs/stable/clients/c/overview
 *
 * Performance is not the best here. You should select only
 * the values that you need instead of using a 'select *' query
 * to actually retrieve only one from the result,
 * but the example works to show how is data stored internally.
 *
 */

require __DIR__ . '/../vendor/autoload.php';

use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Result\DataChunk;

$duckDB = DuckDB::create();

$duckDB->query("CREATE TABLE example_4 (column1 VARCHAR, column2 VARCHAR, column3 VARCHAR);");
$duckDB->query("INSERT INTO example_4 (column1, column2, column3) VALUES ('0x0', '0x1', '0x2'), ('1x0', '1x1', '1x2'), ('2x0', '2x1', '2x2'), ('3x0', '3x1', '3x2');");

$result = $duckDB->query("SELECT * FROM example_4");

$value3x1 = get_value(['row' => 3, 'column' => 1], $result);

printf("%s is the value in position 3,1\n", $value3x1);

function get_value(array $position, \Saturio\DuckDB\Result\ResultSet $resultSet): string
{
    $rowsInPreviousChunks = 0;
    /** @var DataChunk $chunk */
    foreach ($resultSet->chunks() as $chunk) {
        $rowCount = $chunk->rowCount();
        $columnCount = $chunk->columnCount();

        if ($columnCount < $position['column']) {
            throw new Exception('Column required is out of range');
        }

        if ($rowCount + $rowsInPreviousChunks < $position['row']) {
            $rowsInPreviousChunks += $rowCount;
            continue;
        }

        $vector = $chunk->getVector($position['column'], rows: $rowCount);
        $dataGenerator = $vector->getDataGenerator();

        for ($rowIndex = 0; $rowIndex < $rowCount; ++$rowIndex) {
            $realRowIndex = $rowsInPreviousChunks + $rowIndex;
            if ($realRowIndex === $position['row']) {
                return $dataGenerator->current();
            }
            $dataGenerator->next();
        }
    }
    throw new Exception('Row required is out of range');
}
