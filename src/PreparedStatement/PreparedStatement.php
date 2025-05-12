<?php

declare(strict_types=1);

namespace Saturio\DuckDB\PreparedStatement;

use DateMalformedStringException;
use Saturio\DuckDB\Exception\BindValueException;
use Saturio\DuckDB\Exception\PreparedStatementExecuteException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Result\ResultSet;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\Type;

class PreparedStatement
{
    private NativeCData $preparedStatement;
    private TypeConverter $converter;

    private function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly NativeCData $connection,
        private readonly string $query,
    ) {
        $this->converter = new TypeConverter($this->ffi, MathLib::create());
    }

    public static function create(
        FFIDuckDB $ffi,
        NativeCData $connection,
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
     * @throws DateMalformedStringException
     */
    public function bindParam(int $parameter, mixed $value, ?Type $type = null): void
    {
        $value = $this->converter->getDuckDBValue($value, $type);
        $status = $this->ffi->bindValue(
            $this->preparedStatement,
            $parameter,
            $value,
        );

        $this->ffi->destroyValue($this->ffi->addr($value));

        if ($status === $this->ffi->error()) {
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
