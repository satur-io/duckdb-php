# Getting started

## Install

You can install the DuckDB PHP package and all the required resources
using Composer by requiring the plugin installer:

```bash
$ composer require satur.io/duckdb-auto
```

This is the recommended option for newcomers and should be enough to 
start using DuckDB from PHP. For more advanced options, 
check other [installation](installation.md) methods.

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
