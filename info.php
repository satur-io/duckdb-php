<?php
require_once __DIR__ . '/vendor/autoload.php';
use Saturio\DuckDB\Exception\NotSupportedException;
use Saturio\DuckDB\FFI\FindLibrary;

echo php_uname();
echo "\n\n\n";

$os = php_uname('s');
$machine = php_uname('m');

printf("Os: %s, Machine: %s\n\n\n", $os, $machine);
try {
    printf("Header: %s\nLib path: %s\n", FindLibrary::headerPath(), FindLibrary::libPath());
} catch (NotSupportedException $e) {
    echo $e->getMessage();
}
