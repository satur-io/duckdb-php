<img alt="DuckDB logo" src="docs/DuckDB-PHP-logo-noborders.svg" height="150">

## DuckDB API for PHP

[![Github Actions Badge](https://github.com/satur-io/duckdb-php/actions/workflows/php_test_main.yml/badge.svg?branch=main)](https://github.com/satur-io/duckdb-php/actions)
[![Github Actions Badge](https://github.com/satur-io/duckdb-php/actions/workflows/php_test_nightly.yml/badge.svg?branch=main)](https://github.com/satur-io/duckdb-php/actions)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=satur-io_duckdb-php&metric=alert_status&token=4a4bd82eff843d2b4a93bf4552b6db78e598ecfa)](https://sonarcloud.io/summary/new_code?id=satur-io_duckdb-php)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=satur-io_duckdb-php&metric=coverage)](https://sonarcloud.io/summary/new_code?id=satur-io_duckdb-php)
[![Packagist Version](https://img.shields.io/packagist/v/satur.io/duckdb?style=flat&logo=packagist&logoColor=white)](https://packagist.org/packages/satur.io/duckdb)
![DuckDB C API Version](https://img.shields.io/badge/DuckDB_C_API-v1.4.1-%23FFF100?logo=duckdb)


This package provides a [DuckDB](https://github.com/duckdb/duckdb) Client API for PHP.

Focused on performance, it uses the official [C API](https://duckdb.org/docs/api/c/overview.html) internally through [FFI](https://www.php.net/manual/en/book.ffi.php), achieving good benchmarks.
This library is more than just a wrapper for the C API; it introduces custom, PHP-friendly methods to simplify working with DuckDB. It is compatible with Linux, Windows, and macOS, requiring PHP version 8.3 or higher.

Full documentation is available in [https://duckdb-php.readthedocs.io/](https://duckdb-php.readthedocs.io/).

### Install

#### Automatic install (recommended for newcomers)
```shell
composer require satur.io/duckdb-all
```
> [!NOTE]
> You will need to trust `satur.io/duckdb-all` to execute code to use this installation method,
> check [installation](https://duckdb-php.readthedocs.io/en/latest/installation) for more details.

### Quick Start

```php
DuckDB::sql("SELECT 'quack' as my_column")->print();    
```

```
-------------------
| my_column       |
-------------------
| quack           |
-------------------
```

> It's that simple! :duck:

The function we used here, `DuckDB::sql()`, performs the query in a new
in-memory database which is destroyed after retrieving the result.

This is not the most common use case, let's see how to get a persistent connection.

#### Connection

```php
$duckDB = DuckDB::create('duck.db'); // or DuckDB::create() for in-memory database

$duckDB->query('CREATE TABLE test (i INTEGER, b BOOL, f FLOAT);');
$duckDB->query('INSERT INTO test VALUES (3, true, 1.1), (5, true, 1.2), (3, false, 1.1), (3, null, 1.2);');

$duckDB->query('SELECT * FROM test')->print();
```

As you probably guessed, `DuckDB::create()` creates a new connection to the specified database,
or create a new one if it doesn't exist yet and then establishes the connection.

After that, we can use the function `query` to perform the requests.

> [!WARNING]
> Notice the difference between the static method `sql` and the non-static method `query`.
> While the first one always creates and destroys a new in-memory database, the second one
> uses a previously established connection and should be the preferred option in most cases.

In addition, the library also provides prepared statements for binding parameters to our query.

#### Prepared Statements
```php
$duckDB = DuckDB::create();

$duckDB->query('CREATE TABLE test (i INTEGER, b BOOL, f FLOAT);');
$duckDB->query('INSERT INTO test VALUES (3, true, 1.1), (5, true, 1.2), (3, false, 1.1), (3, null, 1.2);');

$boolPreparedStatement = $duckDB->preparedStatement('SELECT * FROM test WHERE b = $1');
$boolPreparedStatement->bindParam(1, true);
$result = $boolPreparedStatement->execute();
$result->print();

$intPreparedStatement = $duckDB->preparedStatement('SELECT * FROM test WHERE i = ?');
$intPreparedStatement->bindParam(1, 3);
$result = $intPreparedStatement->execute();
$result->print();
```

#### Appenders
Appenders are the preferred method to load data in DuckDB. See [DuckDB docs](https://duckdb.org/docs/stable/clients/c/appender.html)
for more information.

```php
$duckDB = DuckDB::create();
$result = $duckDB->query('CREATE TABLE people (id INTEGER, name VARCHAR);');

$appender = $duckDB->appender('people');

for ($i = 0; $i < 100; ++$i) {
    $appender->append(rand(1, 100000));
    $appender->append('string-'.rand(1, 100));
    $appender->endRow();
}

$appender->flush();
```


#### DuckDB powerful

DuckDB provides some amazing features. For example, 
you can query remote files directly.

Let's use an aggregate function to calculate the average of a column
for a parquet remote file:

```php
DuckDB::sql(
    'SELECT "Reporting Year", avg("Gas Produced, MCF") as "AVG Gas Produced" 
                FROM "https://github.com/plotly/datasets/raw/refs/heads/master/oil-and-gas.parquet" 
                WHERE "Reporting Year" BETWEEN 1985 AND 1990
                GROUP BY "Reporting Year";'
)->print();
```

```
--------------------------------------
| Reporting Year   | AVG Gas Produce |
--------------------------------------
| 1985             | 2461.4047344111 |
| 1986             | 6060.8575605681 |
| 1987             | 5047.5813074014 |
| 1988             | 4763.4090541633 |
| 1989             | 4175.2989758837 |
| 1990             | 3706.9404742437 |
--------------------------------------
```

Or summarize a remote csv:

```php
DuckDB::sql('SUMMARIZE TABLE "https://blobs.duckdb.org/data/Star_Trek-Season_1.csv";')->print();
```

```
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
| column_name      | column_type      | min              | max              | approx_unique    | avg              | std              | q25              | q50              | q75              | count            | null_percentage |
------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
| season_num       | BIGINT           | 1                | 1                | 1                | 1.0              | 0.0              | 1                | 1                | 1                | 30               | 0               |
| episode_num      | BIGINT           | 0                | 29               | 29               | 14.5             | 8.8034084308295  | 7                | 14               | 22               | 30               | 0               |
| aired_date       | DATE             | 1965-02-28       | 1967-04-13       | 35               |                  |                  | 1966-10-20       | 1966-12-22       | 1967-02-16       | 30               | 0               |
| cnt_kirk_hookup  | BIGINT           | 0                | 2                | 3                | 0.3333333333333  | 0.6064784348631  | 0                | 0                | 1                | 30               | 0               |

...

```

I would recommend taking a look at the [DuckDB documentation](https://duckdb.org/docs/stable/sql/introduction) to figure out
all possibilities.

> [!TIP]
> Do you want more use cases? Check the [examples folder](examples).

### Requirements
- Linux, macOS, or Windows
- x64 platform
- PHP >= 8.3
- ext-ffi

#### Recommended
- ext-bcmath - Needed for big integers (> PHP_INT_MAX)
- ext-zend-opcache - For better performance

### Type Support

From version 1.2.0 on the library supports all DuckDB file types.

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
| DUCKDB_TYPE_BIGNUM       | BIGNUM       | string                               |
| DUCKDB_TYPE_SQLNULL      | NULL         | null                                 |


### Other PHP DuckDB Integrations

This project takes inspiration from [thbley/php-duckdb-integration](https://github.com/thbley/php-duckdb-integration) and [kambo-1st/duckdb-php](https://github.com/kambo-1st/duckdb-php). Without these prior works, **satur-io/duckdb-php** might not exist.

However, there are some key differences:
- **satur-io/duckdb-php** leverages all modern C API methods, avoiding deprecated ones.
- It supports all major platforms (Linux, macOS, and Windows) and automatically selects the appropriate C library.
- Prioritizes performance.
- Simple to install and use.
- Bundles all necessary resources into a single Composer package.


### Contributions Are Welcome

There are several open issues you can contribute to. Feel free to create new issues for feature requests or bug reports. Contributions of any kind are highly appreciated!

If you'd like to contribute, please follow these steps:
1. Fork the repository.
2. Create a new branch for your feature or bug fix.
3. Commit your changes with clear and concise messages.
4. Submit a pull request with a detailed description of your changes.

> [!NOTE]
> Please include tests for any new functionality or bug fixing.

Thank you for helping improve this project!


<p align="center">
    <a href='https://ko-fi.com/saturio/tip' target='_blank'>
        <img height='36' style='border:0px;height:36px;' src='https://storage.ko-fi.com/cdn/kofi4.png?v=6' border='0' alt='Buy Me a Coffee at ko-fi.com' />
    </a>
</p>
