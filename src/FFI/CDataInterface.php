<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\FFI;

/**
 * @property \FFI\CData $cdata
 */
interface CDataInterface
{
    public function getInternalCData(): string|float|int|bool|\FFI\CData|null;
}
