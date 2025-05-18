# Getting started

## Install

You can install the DuckDB PHP package using Composer by running the following command:

```bash
$ composer require satur.io/duckdb
```

## Query

`Saturio\DuckDB\DuckDB` is the main entrypoint to start using the library.

```php
DuckDB::sql("SELECT 'quack' as my_column")->print();
```

Learn how to establish connections and execute queries in the next section: [Connections and queries](running-queries.md)

## Requirements

- Linux, macOS, or Windows
- x64 platform
- PHP >= 8.3
- `ext-ffi`

While only the `ext-ffi` extension is mandatory to start coding, the `ext-bcmath` extension is highly recommended for managing integers larger than `PHP_INT_MAX`. Without it, any operation involving such integers will result in exceptions.
