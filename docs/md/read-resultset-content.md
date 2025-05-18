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
object. Note that for the moment, only `SELECT` queries return a useful `ResultSet`
value. For other query types, such as `INSERT`, `DELETE`, `UPDATE`, or
`DDL` ones (`CREATE`, `ALTER`, etc.), no specific info is provided, and
you can consider that the query worked if no error was thrown at execution time.

## Column count and column names functions

The `\Saturio\DuckDB\Result\ResultSet` class provides some self-explanatory
functions to retrieve information about the columns, such as their names and count:
`columnName(int $index)`, `columnCount()` and `columnNames()`.

```php
$result = $duckDB->query("SELECT 'quack' as quack_column, 'quick' as 'quick_column';");
print_r(iterator_to_array($result->columnNames()));
```

```shell
Array
(
    [0] => quack_column
    [1] => quick_column
)
```

## Loop over the rows

`\Saturio\DuckDB\Result\ResultSet::rows()` loops over result rows and
is the most common way to get the result values.

```php
$result = $duckDB->query("SELECT * FROM (VALUES ('quack', 'queck'), ('quick', NULL), ('duck', 'cool'));");

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
    Check [types section](types.md) to figure out the expected type
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
    `columnNameAsKey` option could make reading data process slower,
    especially when working with large datasets or executing high-frequency queries,
    as it involves additional processing to map column names to keys. This can also
    increase memory usage. For optimal performance, 
    [column count and column names functions](#column-count-and-column-names-functions)
    are preferred in most cases.

Internally, `rows()` function uses the [C fetch chunks function](https://duckdb.org/docs/stable/clients/c/query#value-extraction)
to get [Data Chunks](https://duckdb.org/docs/stable/clients/c/data_chunk) 
and their [Vectors](https://duckdb.org/docs/stable/clients/c/vector).
This should be the fastest and preferred way to read the result values in general,
but in some cases you could be interested in
[looping over rows in batches](#loop-over-rows-in-batches)
or in [the low level functions](#fetching-chunks-and-vectors-low-level-result-access)
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

This [example](examples.md#get-value-by-row-and-column) 
and `ResultSet::rows()` method implementation
can be also useful to see how this internal methods works.
