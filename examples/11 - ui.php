<?php

require __DIR__ . '/../vendor/autoload.php';
use Saturio\DuckDB\DuckDB;

$duckDB = DuckDB::create('my-ui.db');
$duckDB->query("CALL start_ui();");
sleep(120);

