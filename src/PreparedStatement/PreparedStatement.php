<?php

declare(strict_types=1);

namespace Saturio\DuckDB\PreparedStatement;

use Saturio\DuckDB\Exception\BindValueException;
use Saturio\DuckDB\Exception\PreparedStatementExecuteException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Result\ResultSet;
use Saturio\DuckDB\Type\Converter\TypeConverter;

class PreparedStatement
{
    private CDataInterface $preparedStatement;

    private function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly CDataInterface $connection,
        private readonly string $query,
    ) {
    }

    public static function create(
        FFIDuckDB $ffi,
        CDataInterface $connection,
        string $query,
    ): self {
        $newPreparedStatement = new self($ffi, $connection, $query);
        $newPreparedStatement->preparedStatement = $newPreparedStatement->ffi->new('duckdb_prepared_statement');
        $newPreparedStatement->ffi->prepare(
            $newPreparedStatement->connection,
            $newPreparedStatement->query,
            $newPreparedStatement->ffi->addr($newPreparedStatement->preparedStatement)
        );

        return $newPreparedStatement;
    }

    /**
     * @throws BindValueException|UnsupportedTypeException
     */
    public function bindParam(int $parameter, mixed $value): void
    {
        if ($this->ffi->bindValue(
            $this->preparedStatement,
            $parameter,
            TypeConverter::getDuckDBValue($value, $this->ffi)
        ) === $this->ffi->error()) {
            $error = $this->ffi->prepareError($this->preparedStatement);
            throw new BindValueException("Couldn't bind parameter {$parameter} to prepared statement {$this->query}. Error: {$error}");
        }
    }

    public function execute(): ResultSet
    {
        $queryResult = $this->ffi->new('duckdb_result');

        $result = $this->ffi->executePrepared($this->preparedStatement, $this->ffi->addr($queryResult));

        if ($result === $this->ffi->error()) {
            $error = $this->ffi->resultError($this->ffi->addr($queryResult));
            $this->ffi->destroyResult($this->ffi->addr($queryResult));
            throw new PreparedStatementExecuteException($error);
        }

        return new ResultSet($this->ffi, $queryResult);
    }

    public function __destruct()
    {
        $this->ffi->destroyPrepared($this->ffi->addr($this->preparedStatement));
    }
}
