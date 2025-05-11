# Getting started

You can install the package using composer

```bash
$ composer require satur.io/duckdb
```

## Requirements

- Linux, macOS, or Windows
- x64 platform
- PHP >= 8.3
- `ext-ffi`

Only `ext-ffi` extension is mandatory and you could start coding with just that. Nevertheless, `ext-bcmath` is strongly recommended, since it's needed to manage big integers. You will get an exception for those types that involves integers greater than `PHP_INT_MAX` and the extension is missing.

`Saturio\DuckDB\DuckDB` is the main entrypoint to start using the library. Go to next section to make your first query: [Running queries](running-queries)