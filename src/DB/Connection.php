<?php

declare(strict_types=1);

namespace SaturIo\DuckDB\DB;

use SaturIo\DuckDB\Exception\ConnectionException;
use SaturIo\DuckDB\FFI\CDataInterface;
use SaturIo\DuckDB\FFI\DuckDB;

class Connection
{
    public CDataInterface $connection;

    /**
     * @throws ConnectionException
     */
    public function __construct(CDataInterface $db, DuckDB $ffi)
    {
        $this->connection = $ffi->new('duckdb_connection');
        $result = $ffi->connect($db, $ffi->addr($this->connection));
        if ($result === $ffi->error()) {
            $ffi->disconnect($ffi->addr($this->connection));
            $ffi->close($ffi->addr($db));
            throw new ConnectionException('Cannot connect to database');
        }
    }
}
