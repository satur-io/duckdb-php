<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Result;

use SaturIo\DuckDB\FFI\CDataInterface;

trait ValidityTrait
{
    protected function rowIsValid(?CDataInterface $validity, int $index): bool
    {
        return null === $validity || $this->ffi->validityRowIsValid($validity, $index);
        // Supposed to be faster, but it doesn't
        // return $this->newValid($validity, $index);
    }

    protected function newValid(?CDataInterface $validity, int $index): bool
    {
        return null === $validity || ($validity->get(intval($index / 64)) & (1 << $index % 64)) !== 0;
    }
}
