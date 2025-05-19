<?php

require __DIR__ . '/../vendor/autoload.php';
use Saturio\DuckDB\DuckDB;

$json = <<<'JSON'
{
  "name": "Example event",
  "description": "Optional description",
  "startDate": 1413384452,
  "endDate": 1413394452,
  "type": {
    "id": "00000000-0000-1100-0000-000000000011"
  }
}
JSON;

$tmpJson = tempnam(sys_get_temp_dir(), 'duckdb_test_');
$handle = fopen($tmpJson, "w");
fwrite($handle, $json);
fclose($handle);

$duckDB = DuckDB::create('on-the-fly-schema.db');
$duckDB->query("CREATE TABLE events AS FROM read_json('{$tmpJson}');");
unlink($tmpJson);
$duckDB->query("SELECT * FROM events;")->print();
unset($duckDB);
unlink('on-the-fly-schema.db');
