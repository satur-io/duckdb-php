<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class NumericConverter
{
    private NativeCData $intermediateDecimal;

    public function __construct(
        private readonly DuckDB $ffi,
    ) {
        $this->intermediateDecimal = $ffi->new('duckdb_decimal', false);
    }

    public function getFloatFromDecimal(
        NativeCData|int $data,
        NativeCData $logicalType,
    ): float {
        $hugeint = is_scalar($data) ? $this->ffi->doubleToHugeint($data) : $data;

        $this->intermediateDecimal->width = $this->ffi->decimalWidth($logicalType);
        $this->intermediateDecimal->scale = $this->ffi->decimalScale($logicalType);
        $this->intermediateDecimal->value = $hugeint;

        return $this->ffi->decimalToDouble($this->intermediateDecimal);
    }

    public function __destruct()
    {
        $this->ffi->free($this->ffi->addr($this->intermediateDecimal));
    }
}
