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

        // The entry of a NULL list is not set, so its length must not be read.
        $listValidity = $this->ffi->vectorGetValidity($this->vector);
        $listValidity = null === $listValidity ? null : $this->ffi->cast('uint64_t *', $listValidity);

        $totalItems = 0;
        for ($i = 0; $i < $this->rows; ++$i) {
            if ($this->rowIsValid($listValidity, $i)) {
                $totalItems += $listEntry[$i]->length;
            }
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
            if (!$this->rowIsValid($listValidity, $i)) {
                $this->children[] = null;

                continue;
            }

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

    public function getChildren(int $rowIndex): ?array
    {
        return array_key_exists($rowIndex, $this->children) ? $this->children[$rowIndex] : [];
    }
}
