<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\DB;

use SaturIo\DuckDB\Exception\ConnectionException;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB;

class DB
{
    public CDataInterface $db;

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
