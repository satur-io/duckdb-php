# Breaking changes in 2.x version

The main challenge for the duckdb-php package distribution, detailed in
[Issue #12](https://github.com/satur-io/duckdb-php/issues/12), 
is the excessive package size. 
Until version 2.x `satur.io/duckdb` included the PHP code and both the
binary and the header files of the C library for all supported OS within a single package. 
While this ensures a dependency-free, unified installation across different environments, 
it inflates the package size to approximately 277MB. 
Since only one of these libraries is actually needed for the running environment, 
this approach results in a large download and minor runtime latency due to the 
dynamic discovery of the correct library path.

From version 2.x on, `satur.io/duckdb` package only includes PHP code and the
header file, excluding DuckDB C library binaries.
So it is expected to download the binary from the official DuckDB release either
automatically by using the plugin or script provided, or manually.

!!! tip
    See the [installation docs](installation.md) for more info.

To upgrade from 1.x to 2.x you can use the plugin or add the binary installation
scripts to your `composer.json` file.

## Upgrading using the new plugin

Remove `satur.io/duckdb` package.

```shell
composer remove satur.io/duckdb
```

Install new plugin package

```shell
composer require satur.io/duckdb-auto
```

## Upgrading adding scripts to composer.json to download the library binary file

Update `satur.io/duckdb` package.

```shell
composer require satur.io/duckdb:2
```

Add scripts to your `composer.json`
```json
    "scripts": {
        "post-install-cmd": "\\Saturio\\DuckDB\\CLib\\Installer::install",
        "post-update-cmd": "\\Saturio\\DuckDB\\CLib\\Installer::install"
    }
```

And run
```shell
composer install
```
