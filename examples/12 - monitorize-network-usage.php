<?php
require __DIR__ . '/../vendor/autoload.php';
use Saturio\DuckDB\DuckDB;

const CREATE_TABLE_FOR_RAW_DATA_QUERY = "CREATE TABLE network_usage_raw AS FROM read_csv('/tmp/network-use.csv');";
const TRANSFORMATION_QUERY = 'SELECT bucket.col0 as bucket_uuid, current_date() + time as log_created_at, * EXCLUDE(time, col0) FROM VALUES(UUID()) as bucket, network_usage_raw';
const CREATE_TABLE_FOR_ANALYSIS = 'CREATE TABLE network_usage as (' . TRANSFORMATION_QUERY . ');';
const ADD_RAW_DATA_QUERY = "COPY network_usage_raw FROM '/tmp/network-use.csv' (HEADER);";
const ADD_DATA_FOR_ANALYSIS = 'INSERT INTO network_usage (' . TRANSFORMATION_QUERY . ');';
const TRUNCATE_RAW_DATA_QUERY = "TRUNCATE network_usage_raw;";

$duckDB = DuckDB::create('my-network-usage.db');

exec('nettop -PL1 > /tmp/network-use.csv');
$duckDB->query(CREATE_TABLE_FOR_RAW_DATA_QUERY);
unlink('/tmp/network-use.csv');
$duckDB->query(CREATE_TABLE_FOR_ANALYSIS);
$duckDB->query(TRUNCATE_RAW_DATA_QUERY);
$duckDB->query("CALL start_ui();");

while(true) {
    exec('nettop -PL1 > /tmp/network-use.csv');
    $duckDB->query(ADD_RAW_DATA_QUERY);
    unlink('/tmp/network-use.csv');
    $duckDB->query(ADD_DATA_FOR_ANALYSIS);
    $duckDB->query(TRUNCATE_RAW_DATA_QUERY);
    sleep(2);
}

