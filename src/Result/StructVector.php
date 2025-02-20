<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB;

class StructVector implements NestedTypeVector
{
    use ValidityTrait;

    /** @var Vector[] */
    private array $children;
    private CDataInterface $logicalType;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly CDataInterface $vector,
        private readonly int $rows,
        private readonly CDataInterface $parentLogicalType,
    ) {
        foreach (range(0, $this->ffi->structTypeChildCount($this->parentLogicalType) - 1) as $childIndex) {
            $this->children[$childIndex] =
                new Vector(
                    $this->ffi,
                    $this->ffi->structVectorGetChild($this->vector, $childIndex),
                    $this->rows,
                    $this->getStructChildName($childIndex),
                );
        }
    }

    private function getStructChildName(int $index): ?string
    {
        $this->logicalType = $this->ffi->vectorGetColumnType($this->vector);

        // @todo - Must be freed with duckdb_free
        return $this->ffi->string($this->ffi->structTypeChildName($this->logicalType, $index));
    }

    public function getChildren(int $rowIndex): array
    {
        $array = [];

        foreach ($this->children as $child) {
            $validity = $child->getValidity();

            if ($this->rowIsValid($validity, $rowIndex)) {
                $array[$child->name] = $child->isNestedType() ?
                    $child->nestedTypeVector->getChildren($rowIndex)
                    : $child->getTypedData($rowIndex);
            } else {
                $array[$child->name] = null;
            }
        }

        return $array;
    }
}
