<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type\Converter;

use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB;

class NumericConverter
{
    private CDataInterface $intermediateDecimal;

    public function __construct(
        private readonly DuckDB $ffi,
    ) {
        $this->intermediateDecimal = $ffi->new('duckdb_decimal', false);
    }

    public function getFloatFromDecimal(
        CDataInterface|int $data,
        CDataInterface $logicalType,
    ): float {
        $hugeint = is_scalar($data) ? $this->ffi->doubleToHugeint($data) : $data;

        $this->intermediateDecimal->width = $this->ffi->decimalWidth($logicalType);
        $this->intermediateDecimal->scale = $this->ffi->decimalScale($logicalType);
        $this->intermediateDecimal->value = $hugeint->cdata;

        return $this->ffi->decimalToDouble($this->intermediateDecimal);
    }

    public function __destruct()
    {
        isset($this->intermediateDecimal) ?? $this->ffi->free($this->ffi->addr($this->intermediateDecimal));
    }
}
