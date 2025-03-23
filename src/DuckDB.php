<?php

declare(strict_types=1);

namespace Saturio\DuckDB;

use Saturio\DuckDB\DB\Connection;
use Saturio\DuckDB\DB\DB;
use Saturio\DuckDB\Exception\ConnectionException;
use Saturio\DuckDB\Exception\DuckDBException;
use Saturio\DuckDB\Exception\QueryException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\PreparedStatement\PreparedStatement;
use Saturio\DuckDB\Result\CollectMetrics;
use Saturio\DuckDB\Result\ResultSet;

class DuckDB
{
    use CollectMetrics;
    private DB $db;
    private Connection $connection;

    private static FFIDuckDB $ffi;

    private function __construct()
    {
        $this->initCollectMetrics();
        $this->collectMetrics && collect_time($_, 'total');
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
        $this->collectMetrics && collect_time($_, 'query');
        $queryResult = self::$ffi->new('duckdb_result');

        $result = self::$ffi->query($this->connection->connection, $query, self::$ffi->addr($queryResult));

        if ($result === self::$ffi->error()) {
            $error = self::$ffi->resultError(self::$ffi->addr($queryResult));
            self::$ffi->destroyResult(self::$ffi->addr($queryResult));
            throw new QueryException($error);
        }

        return new ResultSet(self::$ffi, $queryResult);
    }

    /**
     * @throws DuckDBException
     */
    public static function sql(string $query): ResultSet
    {
        return self::create()->query($query);
    }

    public function preparedStatement(string $query): PreparedStatement
    {
        return PreparedStatement::create(self::$ffi, $this->connection->connection, $query);
    }

    public function __destruct()
    {
        if (isset($this->connection)) {
            self::$ffi->disconnect(self::$ffi->addr($this->connection->connection));
        }
        if (isset($this->db)) {
            self::$ffi->close(self::$ffi->addr($this->db->db));
        }
    }
}
