<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Result;

use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB;

class MapVector implements NestedTypeVector
{
    private ListVector $list;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly CDataInterface $vector,
        private readonly int $rows,
    ) {
        $this->list = new ListVector(
            $this->ffi,
            $this->vector,
            $this->rows,
        );
    }

    public function getChildren(int $rowIndex): array
    {
        return array_reduce($this->list->getChildren($rowIndex), function ($result, $item) {
            $result[$item['key']] = $item['value'];

            return $result;
        }, []);
    }
}
