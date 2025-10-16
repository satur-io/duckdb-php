# Installation

## Plugin (recommended for newcomers)
```shell
composer require satur.io/duckdb-plugin
```

This will install the `satur.io/duckdb-plugin` package,
and it's the simplest way to have all the required resources at once.

The plugin installs `satur.io/duckdb` and download the necessary DuckDB C library for your OS.

You will need to trust `satur.io/duckdb-plugin` to execute code for this installation method.

You can check the plugin source code [in its repo](https://github.com/satur-io/duckdb-plugin).

If for any reason you don't want to use the plugin, or you prefer a customizable installation,
please use any of the advanced installation options.

## Advanced installation options

Basically, to use this library you need the `satur.io/duckdb` package
and the official C library files provided by DuckDB (both the header file and the binary).

Unfortunately, C libraries are OS and platform dependant. Also, the header file needs some changes
to be used in PHP via FFI.

For each `satur.io/duckdb` release (from v2.0 on), we include the
fixed headers and the binaries for all supported platforms as assets.

!!! quote
You can take a look at `scripts/get_libraries.sh`, the script used to get the libraries
and adapt the headers files, and `.github/workflows/release-libs.yml` the action to publish
the assets.

### Using the downloader script

Install `satur.io/duckdb`
```shell
composer require satur.io/duckdb
```

To download the C libraries for your OS-platform, we provide a simple script.
The script downloads the required files for the current package version in the
desired path:

```shell
./vendor/bin/install-c-lib
```

After running the command, you should see a requirement for setting the `DUCKDB_PHP_PATH`
environment variable to the path where you downloaded the library.

### Downloading the library by yourself

Install `satur.io/duckdb`
```shell
composer require satur.io/duckdb
```

Download C library, for example:
```shell
mkdir -p ~/duckdb-macos-lib && curl -s -L https://github.com/satur-io/duckdb-php/releases/download/1.2.0-beta.1/osx-universal.zip -o /tmp/duckdb.zip && unzip /tmp/duckdb.zip -d ~/duckdb-macos-lib && rm /tmp/duckdb.zip
```

As in the previous case, you need to set the `DUCKDB_PHP_PATH`
environment variable to the path.
```shell
export DUCKDB_PHP_PATH=~/duckdb-macos-lib
```
