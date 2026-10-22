<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class StructVector implements NestedTypeVector
{
    use ValidityTrait;

    /** @var Vector[] */
    private array $children = [];
    private ?NativeCData $logicalType = null;

    public function __construct(
        private readonly DuckDB $ffi,
        private readonly NativeCData $vector,
        private readonly int $rows,
        private readonly NativeCData $parentLogicalType,
    ) {
        $childCount = $this->ffi->structTypeChildCount($this->parentLogicalType);
        for ($childIndex = 0; $childIndex < $childCount; ++$childIndex) {
            $this->children[$childIndex] =
                new Vector(
                    $this->ffi,
                    $this->ffi->structVectorGetChild($this->vector, $childIndex),
                    $this->rows,
                    $this->getStructChildName($childIndex),
                );
        }
    }

    private function getStructChildName(int $index): string
    {
        $this->logicalType = $this->ffi->vectorGetColumnType($this->vector);

        // @todo - Must be freed with duckdb_free
        return $this->ffi->string($this->ffi->structTypeChildName($this->logicalType, $index));
    }

    public function getChildren(int $rowIndex): array
    {
        $unnamed = [] !== $this->children;
        foreach ($this->children as $child) {
            if ('' !== $child->name && null !== $child->name) {
                $unnamed = false;
                break;
            }
        }

        $array = [];

        foreach ($this->children as $child) {
            $validity = $child->getValidity();

            if ($this->rowIsValid($validity, $rowIndex)) {
                $value = $child->isNestedType() ?
                    $child->nestedTypeVector->getChildren($rowIndex)
                    : $child->getTypedData($rowIndex);
            } else {
                $value = null;
            }

            if ($unnamed) {
                $array[] = $value;
            } else {
                $array[$child->name] = $value;
            }
        }

        return $array;
    }

    public function __destruct()
    {
        if (null !== $this->logicalType) {
            $this->ffi->destroyLogicalType($this->ffi->addr($this->logicalType));
        }
    }
}
