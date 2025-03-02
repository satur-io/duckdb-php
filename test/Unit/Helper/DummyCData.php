<?php

declare(strict_types=1);

namespace Unit\Helper;

use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class DummyCData implements CDataInterface
{
    public function getInternalCData(): string|float|int|bool|NativeCData|null
    {
        return null;
    }
}
