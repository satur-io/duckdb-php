<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class InstanceCache
{
    private NativeCData $instanceCache;

    public function __construct(private readonly DuckDB $ffi)
    {
        $this->instanceCache = $ffi->createInstanceCache();
    }

    public function getInstanceCache(): NativeCData
    {
        return $this->instanceCache;
    }

    public function __destruct()
    {
        $this->ffi->destroyInstanceCache($this->ffi->addr($this->getInstanceCache()));
    }
}
