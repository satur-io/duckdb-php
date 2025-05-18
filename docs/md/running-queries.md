# Connections and queries

The simplest way to perform a query is by `\Saturio\DuckDB\DuckDB::sql()` static function.
It creates a new in-memory database connection and runs the query.

```php
DuckDB::sql("SELECT 'quack' as my_column")->print();
```

```bash
-------------------
| my_column       |
-------------------
| quack           |
-------------------
```

It's easy to use, but this is not the most common use case, since you probably want
to set a persistent connection and run more than one query over there.

!!! warning
    Keep in mind that `sql()` creates the connection with a new in-memory database,
    performs the query and immediately after that connection will be released and
    database will be removed so **all data is ephemeral**.

Continue reading below to figure out how to work with connections in duckdb-php.

## Connections

This library is built on top of official DuckDB C API and wraps the main methods
for different connection creation.

For all of them, we will use the `\Saturio\DuckDB\DuckDB::create()` function.


### Regular connection

This is the simplest way to establish a new connection.

```php
// In-memory
$duckDB = DuckDB::create();

// Database file
$duckDB = DuckDB::create('duck.db');
```

Then, you should use the function `\Saturio\DuckDB\DuckDB::query()`
to make queries over the created connection.

```php
$duckDB->query('CREATE TABLE integers (first_int_column INTEGER, second_int_column INTEGER);');
$duckDB->query('INSERT INTO integers VALUES (3, 4), (5, 6), (7, NULL);');

$result = $duckDB->query('SELECT * FROM integers;');
printf("%s columns retrieved: %s\n",
    $result->columnCount(),
    implode(', ', iterator_to_array($result->columnNames())),
);
```

We will talk later about `\Saturio\DuckDB\Result\ResultSet` and
how to read resultset values. But let's see some other ways
to get a DB connection first.


### Connection with configuration

For setting config options during database connection, you only need to add
a `\Saturio\DuckDB\DB\Configuration` object to the `\Saturio\DuckDB\DuckDB::create()`
method.

`\Saturio\DuckDB\DB\Configuration` is just a DTO to store key-value config pairs.
To add a config value, use `\Saturio\DuckDB\DB\Configuration::set` that expects a key-value pair (string, string).

```php
$config = new Configuration();
$config->set('access_mode', 'READ_WRITE');
$config->set('threads', '8');

$duckdb = DuckDB::create(config: $config);

// ... Use $duckdb object as usual
```

### Connection in a instance cache

From DuckDB C API docs:

!!! quote
    _The instance cache is necessary if a client/program (re)opens multiple databases to the same file within the same process._

It creates a new database instance when `instanceCache` is `true` or retrieves an existing database instance.

```php
$duckdbFirstConnection = DuckDB::create(config: $config, instanceCache: true);
// Since instanceCache is true, it creates a new instance
// You can retrieve it to reuse using DuckDB::getInstanceCache()

// ... here you can use $duckdbFirstConnection as usual

// And now we will open a new connection to same database
// using the instance cache we created in the first step
$duckdbSecondConnection = DuckDB::create(
    config: $config,
    instanceCache: $duckdbFirstConnection->getInstanceCache(),
);

// ... here you can use both $duckdbFirstConnection and $duckdbSecondConnection
```

In the next section you will see how to deal with the result for reading queries.
