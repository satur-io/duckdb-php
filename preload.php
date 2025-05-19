<?php
declare(strict_types=1);

require_once __DIR__ . '/src/FFI/FindLibrary.php';

use Saturio\DuckDB\FFI\FindLibrary;

\FFI::load(FindLibrary::headerPath());

opcache_compile_file(__DIR__ . "/src/FFI/DuckDB.php");
