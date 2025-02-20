<?php
declare(strict_types=1);

use Saturio\DuckDB\FFI\FindLibrary;

require __DIR__ . '/vendor/autoload.php';

FFI::load(FindLibrary::headerPath());

opcache_compile_file(__DIR__ . "/src/FFI/DuckDB.php");
