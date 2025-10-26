# Installation

## Composer plugin (recommended for newcomers)
```shell
composer require satur.io/duckdb-auto
```

This will install the `satur.io/duckdb-auto` package,
and it's the simplest way to have all the required resources at once.

The plugin installs the package `satur.io/duckdb` and downloads the necessary DuckDB C library for your OS.

You will need to trust `satur.io/duckdb-auto` to execute code for this installation method.

You can check the plugin source code [in its repo](https://github.com/satur-io/duckdb-auto).

If for any reason you don't want to use the plugin, or you prefer a customizable installation,
please use any of the advanced installation options.

## Advanced installation options

Basically, to use this library you need the `satur.io/duckdb` package
and the official C library files provided by DuckDB (both the header file and the binary).

Unfortunately, C libraries are OS and platform dependant. Also, the header file needs some changes
to be used in PHP via FFI.

From v2.0 on `satur.io/duckdb` includes the fixed headers for all platforms
and a script to download the library from the official DuckDB release.

Key files:
- `scripts/get_header.sh` - the script used to get the headers
and adapt the files to be used with FFI.
- `\Saturio\DuckDB\CLib\Installer::install` - the installer method.
- `install-c-lib` - a simple script useful to install the library in a custom path.

### Adding scripts to composer.json to download the library

Require the package
```shell
composer require satur.io/duckdb
```

Add scripts to your `composer.json`
```json lines
    "scripts": {
        "post-install-cmd": "\\Saturio\\DuckDB\\CLib\\Installer::install",
        "post-update-cmd": "\\Saturio\\DuckDB\\CLib\\Installer::install"
    }
```

And run
```shell
composer install
```

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

If you don't use the default installation path,
after running the command you should see a requirement for setting the `DUCKDB_PHP_PATH`
environment variable to the path where you downloaded the library.

### Downloading the library by yourself
!!! warning
Only recommended for advanced users. All this steps can be automatized using the downloader script described
above.

Install `satur.io/duckdb`
```shell
composer require satur.io/duckdb
```

Create a directory to place the library
```shell
mkdir ~/my-custom-dir
```

Download C library, for example, for osx:
```shell
curl -s -L https://github.com/duckdb/duckdb/releases/download/v1.4.1/libduckdb-osx-universal.zip -o /tmp/duckdb.zip && unzip /tmp/duckdb.zip && rm /tmp/duckdb.zip
```

Copy the library binary file:
```shell
cp libduckdb.dylib ~/my-custom-dir/libduckdb.dylib
```

Copy the fixed header:
```shell
cp vendor/satur.io/header/osx-universal/duckdb-ffi.h ~/my-custom-dir/duckdb-ffi.h
```
!!! warning
If you want to use `FFI::load` and `FFI::scope` (recommended fot a better performance),
you also need to modify the `duckdb-ffi.h` file and include on top `FFI_SCOPE` and `FFI_LIB`.
Check `\Saturio\DuckDB\CLib\Installer::copyHeader`.

As in the previous case, you need to set the `DUCKDB_PHP_PATH`
environment variable to the path.
```shell
export DUCKDB_PHP_PATH=~/my-custom-dir
```
