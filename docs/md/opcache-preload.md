# Improve performance with opcache

[OPcache](https://www.php.net/manual/en/intro.opcache.php) stores precompiled script bytecode in shared memory.
[OPcache preloading](https://www.php.net/manual/en/ffi.examples-complete.php) will improve performance loading the C library.

You will need to add an script in a path reachable by your php runtime.

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/satur.io/src/FFI/FindLibrary.php';

use Saturio\DuckDB\FFI\FindLibrary;

FFI::load(FindLibrary::headerPath());

opcache_compile_file(__DIR__ . "/vendor/satur.io/src/FFI/DuckDB.php");
```

!!! tip
    Adapt the routes according to your system structure.

And also add in yor `php.ini` files opcache configuration:

```shell
ffi.enable=preload
opcache.preload=/path/to/your/preload.php
```

This will preload your `preload.php` script defined in the first step.
Adapt the path to the location of `preload.php`.

In addition, maybe you would like to change some other settings according to
your project and your system. These are the most common options.

```shell
[opcache]
zend_extension=opcache.so
opcache.enable=1
opcache.enable_cli=1
ffi.enable=preload
opcache.preload=/path/to/your/preload.php
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
opcache.jit_buffer_size=256M
```

JIT (Just-In-Time) compilation can significantly improve performance by compiling code into machine code at runtime.
Refer to the [PHP documentation for OPcache](https://www.php.net/manual/en/book.opcache.php) for more detailed information.
