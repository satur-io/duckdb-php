# Appenders

From DuckDB docs:

!!! quote
    _Appenders are the most efficient way of loading data into DuckDB from within the C interface,
    and are recommended for fast data loading.
    The appender is much faster than using prepared statements or individual `INSERT INTO` statements._

## Create an appender for a table

Use `\Saturio\DuckDB\DuckDB::appender()` to create an appender for a table:

```php
$appender = $duckDB->appender(table: 'people');
```

Or set also `schema` and/or `catalog` if needed:

```php
$appender = $duckDB->appender(table: 'people', schema: 'my_schema', catalog: 'db_attached');
```

## Append data flow
Let's assume that we have a table as this one:

```sql
CREATE TABLE people (id INTEGER, name VARCHAR);
```

And we created an appender this way:

```php
$appender = $duckDB->appender(table: 'people');
```

First we will need to append data.
The appender expects the data values in order, so for this case
we will need to append first the `id` and after that the `name`
to append a row:

```php
$appender->fastAppend(1);
$appender->fastAppend('Daniel Hernández-Marín');
```

And when we are done with a row, we should call `endRow()` function:

```php
$appender->endRow();
```

Alternately, you can use `appendRow` to append a full row:
```php
$appender->appendRow(2, 'Elena de Nicolás');
```

`fastAppend` and `appendRow` methods are simple and quite efficient,
but for the best performance maybe you want to use a specific-type append method:

```php
$appender->appendInt(3);
$appender->appendVarchar('Carlos Hernández Romero');
$appender->endRow();
```

And to flush the appended rows to database, we use `flush()` function:

```php
$appender->flush();
```

If flushing the data triggers a constraint violation or any other error, then all data is invalidated.
To handle such errors, you can wrap the `flush()` operation in a try-catch block. It is not possible to append more values when a error is thrown.

And putting all together:

```php
$duckDB->query('CREATE TABLE people (id INTEGER, name VARCHAR);');

$appender = $duckDB->appender(table: 'people');

$appender->fastAppend(1);
$appender->fastAppend('Daniel Hernández-Marín');
$appender->endRow();

$appender->appendRow(2, 'Elena de Nicolás');

$appender->appendInt(3);
$appender->appendVarchar('Carlos Hernández Romero');
$appender->endRow();

$appender->flush();
```
