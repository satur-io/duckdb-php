<?php

declare(strict_types=1);

if (!class_exists('Saturio\DuckDB\Native\FFI\CData', false)) {
    class_alias(FFI\CData::class, 'Saturio\DuckDB\Native\FFI\CData');
}
if (!class_exists('Saturio\DuckDB\Native\FFI', false)) {
    class_alias(FFI::class, 'Saturio\DuckDB\Native\FFI');
}
