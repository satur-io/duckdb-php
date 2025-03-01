<?php

if (getenv('DUCKDB_PHP_LIB_USE_WRAPPERS')) {
    class_alias(\Saturio\DuckDB\FFI\CDataInterface::class, 'Saturio\DuckDB\Native\FFI\CData');
    class_alias(\Saturio\DuckDB\FFI\FFIInterface::class, 'Saturio\DuckDB\Native\FFI');

} else {
    class_alias(\FFI\CData::class, 'Saturio\DuckDB\Native\FFI\CData');
    class_alias(\FFI::class, 'Saturio\DuckDB\Native\FFI');
}