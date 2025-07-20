# Production-optimized installation

!!! tip
If you are just testing the library or working locally, you don't need this yet.
Our recommendation is to start with the simple `composer require satur.io/duckdb` and back here before deploying.
You can run this steps at any point.

The library requires the proper DuckDB C library according to the OS.
The main package, `satur.io/duckdb`, does not contain any C library, but
it's configured to require also `satur.io/duckdb-clib-all` in the default behaviour.

That means you are downloading the C libraries for all operative systems
but only one is actually required (and used).

This is made in this way to facilitate start coding, but it requires
~270MB of storage which we can reduce significantly.

To automatically detect your system and require only the proper package
you can use the https://duckdb-install.satur.io script in this way:

```shell
$(php -r "readfile('https://duckdb-install.satur.io');" | php)
```

Alternatively, you can specify the desired package, e.g.:

```shell
composer require satur.io/duckdb satur.io/duckdb-clib-linux-amd64
```

Check in packagist the [packages providing satur.io/duckdb-clib](https://packagist.org/providers/satur.io/duckdb-clib).

## Installing the library manually

If you want to install the library by yourself instead of using a package
you can require `satur.io/duckdb-clib-no-lib` package and set both
`DUCKDB_PHP_HEADER_PATH` and `DUCKDB_PHP_LIB_PATH`.

```shell
mkdir -p ~/duckdb-linux-lib && curl -s -L https://github.com/satur-io/duckdb-php-clib-linux-amd64/archive/refs/tags/0.0.6.tar.gz | tar xvz --strip-components 1 -C ~/duckdb-linux-lib
DUCKDB_PHP_HEADER_PATH=~/duckdb-linux-lib/duckdb-ffi.h
DUCKDB_PHP_LIB_PATH=~/duckdb-linux-lib/libduckdb.so
composer require satur.io/duckdb satur.io/duckdb-clib-no-lib
```
