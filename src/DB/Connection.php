<?php

declare(strict_types=1);

namespace Saturio\DuckDB\DB;

use Saturio\DuckDB\Exception\ConnectionException;
use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB;

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
