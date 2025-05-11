# Resultset values

The main challenge wrapping DuckDB C client API for PHP is
data type conversion. When we run a query, the C client stores the full (materialized) result
into a pointer without almost any performance overhead. But this data is
stored in C types that should be converted to PHP types. This conversion,
although is optimized, have a unavoidable run-time increase.

!!! abstract
    Type conversion is carried out when the data is read and not during query execution.
    Take this into account for reading the results.

DuckDB C client provides [two different ways to access data](https://duckdb.org/docs/stable/clients/c/query#value-extraction): 
The `duckdb_value` functions and `duckdb_fetch_chunk`. As explained in DuckDB documentation,
the `duckdb_value` functions are slower than fetching chunks and are deprecated,
so they are not used in this library.

The result of a query is stored in PHP layer in a `\Saturio\DuckDB\Result\ResultSet`
object[^1].

## Column count and column names functions

The `\Saturio\DuckDB\Result\ResultSet` provides some auto-explanatory
functions to get info about the columns retrieved:
`columnName(int $index)`, `columnCount()` and `columnNames()`. 

## Loop over the rows

`\Saturio\DuckDB\Result\ResultSet::rows()` loops over result rows and
is the most common way to get the result values.

```php
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
```

!!! tip
    Check [types section](#types) to figure out the expected type
    for each value.

You can also set the `columnNameAsKey` param as true to get the column
name as the key of the array that represents each row:

```php
$result = $duckDB->query( "SELECT 'quack' as column1, 'queck' as column2, 'quick' as column3;");

foreach ($result->rows(columnNameAsKey: true) as $row) {
    foreach ($row as $column => $value) {
        echo "{$column}: {$value}" . "\n";
    }
}
```
!!! tip
    `columnNameAsKey` option could make reading data process slower
    and increase memory usage. For optimal performance
    [column count and column names functions](#column-count-and-column-names-functions)
    are preferred in most cases.

Internally, `rows()` function uses the [C fetch chunks function](https://duckdb.org/docs/stable/clients/c/query#value-extraction)
to get [Data Chunks](https://duckdb.org/docs/stable/clients/c/data_chunk) 
and their [Vectors](https://duckdb.org/docs/stable/clients/c/vector). This should be the fastest way to read the result
values, but in some cases you could be interested in
[looping over rows in batches](#loop-over-rows-in-batches)
or in [the low level functions](#fetching-chunks-and-vectors---low-level-result-access)
to get more control.

## Loop over rows in batches

In some cases, looping in batches could be faster. Since in `rows()`
function data type conversion is performed per each row when you read
them, you can retrieve a chunk and convert the types per each vector
returning all data converted for that chunk on each loop iteration.

```php
foreach ($result->vectorChunk() as $rowBatch) {
    $rows = sizeof($rowBatch[0]);

    for ($i = 0; $i < $rows; $i++) {
        foreach ($rowBatch as $columnIndex => $column) {
            printf("%s\n", $column[$i]);
        }
    }
}
```

## Fetching chunks and vectors - Low level result access

`ResultSet` allows also low level control for reading values.
For example, you can use `fetchChunk()` to get a `DataChunk` 
object and their `getVector()` function to get a `Vector`.

`DataChunk` and `Vector` objects are analogous to `duckdb_data_chunk`
and `duckdb_vector` C types. You probably want to check 
[DuckDB documentation](https://duckdb.org/docs/stable/clients/c)
to understand what these objects represent and how to use them.

## Types

Since version 1.3.0 the library supports all DuckDB file types.

| DuckDB Type              | SQL Type     | PHP Type                             |
|--------------------------|--------------|--------------------------------------|
| DUCKDB_TYPE_BOOLEAN      | BOOLEAN      | bool                                 |
| DUCKDB_TYPE_TINYINT      | TINYINT      | int                                  |
| DUCKDB_TYPE_SMALLINT     | SMALLINT     | int                                  |
| DUCKDB_TYPE_INTEGER      | INTEGER      | int                                  |
| DUCKDB_TYPE_BIGINT       | BIGINT       | int                                  |
| DUCKDB_TYPE_UTINYINT     | UTINYINT     | int                                  |
| DUCKDB_TYPE_USMALLINT    | USMALLINT    | int                                  |
| DUCKDB_TYPE_UINTEGER     | UINTEGER     | int                                  |
| DUCKDB_TYPE_UBIGINT      | UBIGINT      | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_FLOAT        | FLOAT        | float                                |
| DUCKDB_TYPE_DOUBLE       | DOUBLE       | float                                |
| DUCKDB_TYPE_TIMESTAMP    | TIMESTAMP    | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_DATE         | DATE         | Saturio\DuckDB\Type\Date             |
| DUCKDB_TYPE_TIME         | TIME         | Saturio\DuckDB\Type\Time             |
| DUCKDB_TYPE_INTERVAL     | INTERVAL     | Saturio\DuckDB\Type\Interval         |
| DUCKDB_TYPE_HUGEINT      | HUGEINT      | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_UHUGEINT     | UHUGEINT     | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_VARCHAR      | VARCHAR      | string                               |
| DUCKDB_TYPE_BLOB         | BLOB         | Saturio\DuckDB\Type\Blob             |
| DUCKDB_TYPE_TIMESTAMP_S  | TIMESTAMP_S  | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_TIMESTAMP_MS | TIMESTAMP_MS | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_TIMESTAMP_NS | TIMESTAMP_NS | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_UUID         | UUID         | Saturio\DuckDB\Type\UUID             |
| DUCKDB_TYPE_TIME_TZ      | TIMETZ       | Saturio\DuckDB\Type\Time             |
| DUCKDB_TYPE_TIMESTAMP_TZ | TIMESTAMPTZ  | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_DECIMAL      | DECIMAL      | float                                |
| DUCKDB_TYPE_ENUM         | ENUM         | string                               |
| DUCKDB_TYPE_LIST         | LIST         | array                                |
| DUCKDB_TYPE_STRUCT       | STRUCT       | array                                |
| DUCKDB_TYPE_ARRAY        | ARRAY        | array                                |
| DUCKDB_TYPE_MAP          | MAP          | array                                |
| DUCKDB_TYPE_UNION        | UNION        | mixed                                |
| DUCKDB_TYPE_BIT          | BIT          | string                               |
| DUCKDB_TYPE_VARINT       | VARINT       | string                               |

[^1]: For the moment, only `SELECT` queries return a useful `ResulSet`
value. For other query types, such as `INSERT`, `DELETE`, `UPDATE` or
`DDL` ones (`CREATE`, `ALTER`, etc) no specific info is provided and 
you can consider that query worked if no error was thrown at execution time.
