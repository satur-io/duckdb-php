# Prepared statements

From DuckDB docs:

!!! quote
    _A prepared statement is a parameterized query.
    The query is prepared with question marks (?) or dollar symbols ($1) indicating the parameters of the query.
    Values can then be bound to these parameters, after which the prepared statement can be executed using those parameters.
    A single query can be prepared once and executed many times._

!!! warning
    Prepared statements shouldn't be used to insert large amounts of data, as they can lead to performance issues and inefficiencies. For such cases, consider using [Appenders](appenders.md) instead.

## Create a prepared statement

You can create a prepared statement using the `\Saturio\DuckDB\DuckDB::preparedStatement()` function:

```php
$preparedStatement = $this->db->preparedStatement('SELECT * FROM test_data WHERE b = ?');
```

or with dollar symbol
```php
$preparedStatement = $this->db->preparedStatement('SELECT * FROM test_data WHERE b = $1');
```

Both options are equivalent, you can use them interchangeably.

## Bind parameters

DuckDB is strongly typed.
To bind a parameter to a prepared statement the parameter type should be specified.
However, duckdb-php client can infer the type in some cases.
So you can [bind a parameter letting duckdb-php infers the type](#infer-type)
(which can result in an unexpected behaviour in some cases)
or you can [define the type explicitly](#explicit-typing).

!!! info inline end
    Please notice that **parameter index starts from `1`** (and not from `0`)

!!! info
    In both cases, when the bound type doesn't fit with the expected type,
    DuckDB will try to cast the type to the required one if possible.
    For example, `$preparedStatement->bindParam(1, "2");` will work even in
    the case an integer value is expected.

### Infer type

To bind a parameter without defining the type, just use `bindParameter`
with `parameter` and `value` parameters.

```php
$preparedStatement->bindParam(
    parameter: 1,
    value: "my-value",
);
```

### Explicit typing

To bind a parameter using a specific type, include the
type to the `bindParam()` function parameters.

```php
$preparedStatement->bindParam(
    parameter: 1,
    value: 12.3,
    type: Type::DUCKDB_TYPE_DECIMAL,
);
```

`bindParams()` expects a `\Saturio\DuckDB\Type\Type` enum value,
but please notice not all types are valid for binding parameters.
Specifically, nested types, `BIT` and `VARINT` are not allowed.
This is a DuckDB limitation, not a duckdb-php one, as nested types, `BIT` and `VARINT` types are not compatible with parameter binding in DuckDB.
