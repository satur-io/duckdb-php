<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB;

class UnionVector implements NestedTypeVector
{
    private StructVector $struct;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly CDataInterface $vector,
        private readonly int $rows,
        private readonly CDataInterface $logicalType,
    ) {
        $this->struct = new StructVector(
            $this->ffi,
            $this->vector,
            $this->rows,
            $this->logicalType,
        );
    }

    public function getChildren(int $rowIndex): mixed
    {
        $union = $this->struct->getChildren($rowIndex);

        return array_values($union)[$union[''] + 1];
    }
}
