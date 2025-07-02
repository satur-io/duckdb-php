<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class DataChunk
{
    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly NativeCData $dataChunk,
        private readonly bool $reusable = true,
    ) {
    }

    public function rowCount(): int
    {
        return $this->ffi->dataChunkGetSize($this->dataChunk);
    }

    public function columnCount(): int
    {
        return $this->ffi->dataChunkGetColumnCount($this->dataChunk);
    }

    public function getVector(int $columnIndex, ?int $rows = null): Vector
    {
        return new Vector(
            $this->ffi,
            $this->ffi->dataChunkGetVector($this->dataChunk, $columnIndex),
            $rows ?? $this->rowCount()
        );
    }

    public function destroy(): void
    {
        $this->ffi->destroyDataChunk($this->ffi->addr($this->dataChunk));
    }

    public function __destruct()
    {
        if (!$this->reusable) {
            $this->ffi->destroyDataChunk($this->ffi->addr($this->dataChunk));
        }
    }
}
