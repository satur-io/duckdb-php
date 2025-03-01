<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class ListVector implements NestedTypeVector
{
    use ValidityTrait;
    private array $children;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly NativeCData $vector,
        private readonly int $rows,
    ) {
        $listEntry = $this->ffi->cast(
            'duckdb_list_entry *',
            $this->ffi->vectorGetData($this->vector),
        );

        $totalItems = 0;
        for ($i = 0; $i < $this->rows; ++$i) {
            $totalItems += $listEntry[$i]->length;
        }

        $vector = new Vector(
            $this->ffi,
            $this->ffi->listVectorGetChild($this->vector),
            $totalItems,
            null,
        );

        $validity = $vector->getValidity();
        $data = $vector->getDataGenerator();

        for ($i = 0; $i < $this->rows; ++$i) {
            $offset = $listEntry[$i]->offset;
            $length = $listEntry[$i]->length;

            $child = [];
            for ($childIndex = $offset; $childIndex < $offset + $length; ++$childIndex) {
                $currentData = $data->current();
                if ($this->rowIsValid($validity, $childIndex)) {
                    $child[] = $currentData;
                } else {
                    $child[] = null;
                }
                $data->next();
            }

            $this->children[] = $child;
        }
    }

    public function getChildren(int $rowIndex): array
    {
        return $this->children[$rowIndex] ?? [];
    }
}
