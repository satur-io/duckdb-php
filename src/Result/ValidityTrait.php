<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\Native\FFI\CData as NativeCData;

trait ValidityTrait
{
    protected function rowIsValid(?NativeCData $validity, int $index): bool
    {
        return null === $validity || $this->ffi->validityRowIsValid($validity, $index);
        // Supposed to be faster, but it doesn't
        // return $this->newValid($validity, $index);
    }

    protected function newValid(?NativeCData $validity, int $index): bool
    {
        return null === $validity || ($validity[intval($index / 64)] & (1 << $index % 64)) !== 0;
    }
}
