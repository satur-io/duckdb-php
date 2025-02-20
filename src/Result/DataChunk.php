<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;

class DataChunk
{
    private CDataInterface $currentVector;

    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly CDataInterface $dataChunk,
        private readonly bool $reusable = true,
    ) {
        $this->currentVector = $this->ffi->new('duckdb_vector');
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
        $this->ffi->dataChunkGetVectorToCDataInterface($this->dataChunk, $columnIndex, $this->currentVector);

        return new Vector(
            $this->ffi,
            $this->currentVector,
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
