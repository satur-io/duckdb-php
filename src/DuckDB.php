<?php

declare(strict_types=1);

namespace Saturio\DuckDB;

use Saturio\DuckDB\Appender\Appender;
use Saturio\DuckDB\DB\Configuration;
use Saturio\DuckDB\DB\Connection;
use Saturio\DuckDB\DB\DB;
use Saturio\DuckDB\DB\InstanceCache;
use Saturio\DuckDB\Exception\ConnectionException;
use Saturio\DuckDB\Exception\DuckDBException;
use Saturio\DuckDB\Exception\ErrorCreatingNewAppender;
use Saturio\DuckDB\Exception\ErrorCreatingNewConfig;
use Saturio\DuckDB\Exception\InvalidConfigurationOption;
use Saturio\DuckDB\Exception\QueryException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\PreparedStatement\PreparedStatement;
use Saturio\DuckDB\Result\Metric\NativeMetric;
use Saturio\DuckDB\Result\Metric\TimeMetric;
use Saturio\DuckDB\Result\ResultSet;

class DuckDB
{
    private DB $db;
    private Connection $connection;
    private ?InstanceCache $instanceCache = null;

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
     * @throws ErrorCreatingNewConfig
     * @throws InvalidConfigurationOption
     */
    private function db(?string $path = null, ?Configuration $config = null): self
    {
        $this->db = new DB(self::$ffi, $path, $config, $this->instanceCache);

        return $this;
    }

    private static function init(): void
    {
        self::$ffi = new FFIDuckDB();
    }

    /**
     * @throws ConnectionException
     * @throws ErrorCreatingNewConfig
     * @throws InvalidConfigurationOption
     */
    public static function create(
        ?string $path = null,
        ?Configuration $config = null,
        InstanceCache|true|null $instanceCache = null,
    ): self {
        $duckDB = new self();
        $duckDB->instanceCache = true === $instanceCache ? new InstanceCache(self::$ffi) : $instanceCache;

        return $duckDB->db($path, config: $config)->connect();
    }

    /**
     * Run a query using the connection established when DuckDB object was created.
     *
     * @throws DuckDBException
     */
    public function query(string $query): ResultSet
    {
        $metric = TimeMetric::create();

        $metric->switch();
        $queryResult = self::$ffi->new('duckdb_result');
        $result = self::$ffi->query($this->connection->connection, $query, self::$ffi->addr($queryResult));
        $metric->switch();

        if ($result === self::$ffi->error()) {
            $error = self::$ffi->resultError(self::$ffi->addr($queryResult));
            self::$ffi->destroyResult(self::$ffi->addr($queryResult));
            throw new QueryException($error);
        }

        $metric->setQueryLatency($this->getLatency());

        return new ResultSet(self::$ffi, $queryResult, $metric);
    }

    /**
     * Run a query in a new in-memory database. The database will be destroyed after retrieving the result.
     *
     * Created mainly for testing purposes. But in some cases,
     * it could be also a good and shorter option
     * for reading data from a file (e.g. csv, json or parquet).
     *
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

    /**
     * @throws ErrorCreatingNewAppender
     */
    public function appender(string $table, ?string $schema = null, ?string $catalog = null): Appender
    {
        return Appender::create(self::$ffi, $this->connection->connection, $table, $schema, $catalog);
    }

    public function getInstanceCache(): ?InstanceCache
    {
        return $this->instanceCache;
    }

    public function getLatency(): float
    {
        return NativeMetric::getLatency(self::$ffi, $this->connection);
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
