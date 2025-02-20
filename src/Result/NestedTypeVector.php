<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

interface NestedTypeVector
{
    public function getChildren(int $rowIndex): mixed;
}
