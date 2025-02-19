<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\Result;

use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB;

class ArrayVector implements NestedTypeVector
{
    use ValidityTrait;
    private array $children;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly CDataInterface $vector,
        private readonly int $rows,
        private readonly CDataInterface $logicalType,
    ) {
        $child = $this->ffi->arrayVectorGetChild($this->vector);
        $arraySize = $this->ffi->arraySize($this->logicalType);
        $totalItems = $arraySize * $rows;

        $vector = new Vector(
            $this->ffi,
            $child,
            $totalItems,
            null,
        );

        $validity = $vector->getValidity();
        $data = $vector->getDataGenerator();

        for ($i = 0; $i < $this->rows; ++$i) {
            $offset = $i * $arraySize;
            $child = [];
            for ($childIndex = $offset; $childIndex < $offset + $arraySize; ++$childIndex) {
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
