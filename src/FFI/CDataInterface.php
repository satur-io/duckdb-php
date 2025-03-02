<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use Saturio\DuckDB\Native\FFI\CData as NativeCData;

/**
 * @property NativeCData $cdata
 */
interface CDataInterface
{
    public function getInternalCData(): string|float|int|bool|NativeCData|null;
}
