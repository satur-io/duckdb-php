<?php

use Saturio\DuckDB\FFI\FindLibrary;

require $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

echo FindLibrary::libPath();
