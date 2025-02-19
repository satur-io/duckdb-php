<?php

declare(strict_types=1);

namespace SaturIo\DuckDB;

use SaturIo\DuckDB\DB\Connection;
use SaturIo\DuckDB\DB\DB;
use SaturIo\DuckDB\Exception\ConnectionException;
use SaturIo\DuckDB\Exception\DuckDBException;
use SaturIo\DuckDB\Exception\QueryException;
use SaturIo\DuckDB\FFI\DuckDB as FFIDuckDB;
use SaturIo\DuckDB\PreparedStatement\PreparedStatement;
use SaturIo\DuckDB\Result\ResultSet;

class DuckDB
{
    private DB $db;
    private Connection $connection;

    private static FFIDuckDB $ffi;

    private function __construct()
    {
        self::init();
    }

    /**
     * @throws ConnectionException
     */
    private function connect(): self
    {
        $this->connection = new Connection($this->db->db, self::$ffi);

        return $this;
    }

    /**
     * @throws ConnectionException
     */
    private function db(?string $path = null): self
    {
        $this->db = new DB(self::$ffi, $path);

        return $this;
    }

    private static function init(): void
    {
        self::$ffi = new FFIDuckDB();
    }

    /**
     * @throws ConnectionException
     */
    public static function create(?string $path = null): self
    {
        return (new self())->db($path)->connect();
    }

    /**
     * @throws DuckDBException
     */
    public function query(string $query): ResultSet
    {
        $queryResult = self::$ffi->new('duckdb_result');

        $result = self::$ffi->query($this->connection->connection, $query, self::$ffi->addr($queryResult));

        if ($result === self::$ffi->error()) {
            $error = self::$ffi->resultError(self::$ffi->addr($queryResult));
            self::$ffi->destroyResult(self::$ffi->addr($queryResult));
            throw new QueryException($error);
        }

        return new ResultSet(self::$ffi, $queryResult);
    }

    public function preparedStatement(string $query): PreparedStatement
    {
        return PreparedStatement::create(self::$ffi, $this->connection->connection, $query);
    }

    public function __destruct()
    {
        isset($this->connection) ?? self::$ffi->disconnect(self::$ffi->addr($this->connection->connection));
        isset($this->db) ?? self::$ffi->close(self::$ffi->addr($this->db->db));
    }
}
