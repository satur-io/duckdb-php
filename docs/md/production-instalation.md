# Production-optimized installation

!!! tip
If you are just testing the library or working locally, you don't need this yet.
Our recommendation is to start with the simple `composer require satur.io/duckdb` and back here before deploying.
You can run this steps at any point.

By default, 

```shell
$(php -r "readfile('https://duckdb-install.satur.io');" | php)
```

```shell
composer require satur.io/duckdb satur.io/duckdb-clib-linux-amd64
```

