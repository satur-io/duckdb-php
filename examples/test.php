<?php

$duckDbFFI = FFI::load('lib/osx-universal/duckdb-ffi.h');

$database   = $duckDbFFI->new("duckdb_database");
$connection = $duckDbFFI->new("duckdb_connection");

$result = $duckDbFFI->duckdb_open(null, FFI::addr($database));

if ($result === $duckDbFFI->DuckDBError) {
    $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
    $duckDbFFI->duckdb_close(FFI::addr($database));
    throw new Exception('Cannot open database');
}

$result = $duckDbFFI->duckdb_connect($database, FFI::addr($connection));
if ($result === $duckDbFFI->DuckDBError) {
    $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
    $duckDbFFI->duckdb_close(FFI::addr($database));
    throw new Exception('Cannot connect to database');
}

$result = $duckDbFFI->duckdb_query($connection, 'CREATE TABLE integers(i INTEGER, j INTEGER);', null);
if ($result === $duckDbFFI->DuckDBError) {
    $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
    $duckDbFFI->duckdb_close(FFI::addr($database));
    throw new Exception('Cannot execute query');
}

$result = $duckDbFFI->duckdb_query($connection, 'INSERT INTO integers VALUES (3,4), (5,6), (7, NULL) ', null);
if ($result === $duckDbFFI->DuckDBError) {
    $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
    $duckDbFFI->duckdb_close(FFI::addr($database));
    throw new Exception('Cannot execute query');
}

$queryResult = $duckDbFFI->new('duckdb_result');

$result = $duckDbFFI->duckdb_query($connection, 'SELECT * FROM integers; ', FFI::addr($queryResult));

if ($result === $duckDbFFI->DuckDBError) {
    $error = "Error in query: ".$duckDbFFI->duckdb_result_error(FFI::addr($queryResult));

    $duckDbFFI->duckdb_destroy_result(FFI::addr($queryResult));
    $duckDbFFI->duckdb_disconnect(FFI::addr($connection));
    $duckDbFFI->duckdb_close(FFI::addr($database));
    throw new Exception($error);
}

// echo "Number of columns: ".$queryResult->__deprecated_column_count."\n";

$rowCount = $duckDbFFI->duckdb_row_count(FFI::addr($queryResult));
$columnCount = $duckDbFFI->duckdb_column_count(FFI::addr($queryResult));

for ($row = 0; $row < $rowCount; $row++) {
    for ($column = 0; $column < $columnCount; $column++) {
        $value = $duckDbFFI->duckdb_value_varchar(FFI::addr($queryResult), $column, $row);
        echo ($value !== null ? FFI::string($value)  : '')." ";
        $duckDbFFI->duckdb_free($value);
    }

    echo "\n";
}

$duckDbFFI->duckdb_destroy_result(FFI::addr($queryResult));
$duckDbFFI->duckdb_disconnect(FFI::addr($connection));
$duckDbFFI->duckdb_close(FFI::addr($database));
