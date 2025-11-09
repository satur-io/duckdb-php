<?php
declare(strict_types=1);

require_once __DIR__ . '/src/FFI/FindLibrary.php';
require_once __DIR__ . '/src/CLib/PlatformInfo.php';

use Saturio\DuckDB\FFI\FindLibrary;

\FFI::load(FindLibrary::headerAndLibrary()[0]);

opcache_compile_file(__DIR__ . "/src/FFI/DuckDB.php");
