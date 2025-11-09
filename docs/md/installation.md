# Installation

## Composer plugin (recommended for newcomers)
```shell
composer require satur.io/duckdb-auto
```

This will install the `satur.io/duckdb-auto` package,
and it's the simplest way to have all the required resources at once.

The plugin installs the package `satur.io/duckdb` and downloads the necessary DuckDB C library for your OS.
You will need to allow `satur.io/duckdb-auto` to execute code for this installation method.
Check the plugin source code [in its own repository](https://github.com/satur-io/duckdb-auto).

If you don't want to use the plugin, or you prefer a customizable installation,
please use any of the advanced installation options.

## Advanced installation options

Basically, to use this library you need the `satur.io/duckdb` package
and the official C library files provided by DuckDB (both the header file and the binary).

Unfortunately, C libraries are OS and platform dependent and the header file needs some changes
to be used in PHP via FFI.

From v2.0 on, `satur.io/duckdb` includes the fixed headers for all platforms
and also a script to download the library binary from the official DuckDB release.

Key files:

- `scripts/get_header.sh` the script used to get the headers and adapt the files to be used with FFI.
- `\Saturio\DuckDB\CLib\Installer::install` the installer method.
- `install-c-lib` a simple script useful to install the library in a custom path.

### Adding scripts to composer.json to download the library

This should be the preferred way if you don't want to use the plugin package,
since it will install the library and it will update it when you update your
PHP package.

Require the package
```shell
composer require satur.io/duckdb
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

### Using the downloader script

!!! warning
    This option gives you more control about the library installation,
    but can result in errors if you don't use it properly.
    Use this option only if you really need it and be careful
    especially when you are going to update the package.

You can also run the download script manually. This is the preferred option
to set a custom path for the library, but it can be problematic when you update
the php package `satur.io/duckdb`.
If the new php package version includes an update of the C library,
you will need to download the library manually again.

Install `satur.io/duckdb`
```shell
composer require satur.io/duckdb
```

To download the C libraries for your OS-platform, we provide a simple script.
The script downloads the required files for the current package version to the
desired path:

```shell
./vendor/bin/install-c-lib
```

If you don't use the default installation path,
after running the command you should see a requirement for setting the `DUCKDB_PHP_PATH`
environment variable to the path where you downloaded the library.

If you want to integrate this method in your CI/CD workflow,
you could use a custom script instead of using the interactive `install-c-lib` command:

!!! tip
    `DUCKDB_PHP_PATH` can be defined both as an environment variable or as a php constant.
    If you define both the environment variable value will be used.

```shell
php -r "require './vendor/autoload.php'; Saturio\DuckDB\CLib\Installer::install(<your-custom-path>);"
echo 'export DUCKDB_PHP_PATH="<your-custom-path>"' >> ~/.bashrc
```

### Downloading the library by yourself
!!! warning
    Only recommended for advanced users.

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
    If you want to use `FFI::load` and `FFI::scope` (recommended for a better performance),
    you also need to modify the `duckdb-ffi.h` file and include on top `FFI_SCOPE` and `FFI_LIB`.
    Check `\Saturio\DuckDB\CLib\Installer::copyHeader`.

As in the previous case, you need to set the `DUCKDB_PHP_PATH`
environment variable to the path.
```shell
export DUCKDB_PHP_PATH=~/my-custom-dir
```
