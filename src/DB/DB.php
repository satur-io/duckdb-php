<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use Saturio\DuckDB\Exception\ConnectionException;
use Saturio\DuckDB\FFI\DuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class DB
{
    public NativeCData $db;

    /**
     * @throws ConnectionException
     */
    public function __construct(
        DuckDB $ffi,
        ?string $path = null,
    ) {
        $this->db = $ffi->new('duckdb_database');
        $result = $ffi->open($path, $ffi->addr($this->db));

        if ($result === $ffi->error()) {
            $ffi->close($ffi->addr($this->db));
            throw new ConnectionException('Cannot open database');
        }
    }
}
