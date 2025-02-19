<?php

declare(strict_types=1);

namespace Unit\Helper;

use SaturIo\DuckDB\FFI\CDataInterface;

class DummyCData implements CDataInterface
{
    public function getInternalCData(): string|float|int|bool|\FFI\CData|null
    {
        return null;
    }
}
