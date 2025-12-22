<?php

declare(strict_types=1);

namespace Saturio\DuckDB\PreparedStatement;

use DateMalformedStringException;
use Saturio\DuckDB\Exception\BindValueException;
use Saturio\DuckDB\Exception\PreparedStatementExecuteException;
use Saturio\DuckDB\Exception\UnexpectedTypeException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Result\PendingResult;
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
        $result = $newPreparedStatement->ffi->prepare(
            $newPreparedStatement->connection,
            $newPreparedStatement->query,
            $newPreparedStatement->ffi->addr($newPreparedStatement->preparedStatement)
        );

        if ($result == $newPreparedStatement->ffi->error()) {
            $error = $newPreparedStatement->ffi->prepareError($newPreparedStatement->preparedStatement);
            // destructor handles destroying prepared statement
            throw new PreparedStatementExecuteException($error);
        }

        return $newPreparedStatement;
    }

    /**
     * @throws BindValueException|UnsupportedTypeException
     * @throws DateMalformedStringException
     * @throws UnexpectedTypeException
     */
    public function bindParam(int|string $parameter, mixed $value, ?Type $type = null): void
    {
        $index = $this->getParameterIndex($parameter);
        $type = is_null($value) ? Type::DUCKDB_TYPE_SQLNULL :
            $type ?? Type::from($this->ffi->paramType($this->preparedStatement, $index));

        $value = $this->converter->getDuckDBValue($value, $type);
        $status = $this->ffi->bindValue(
            $this->preparedStatement,
            $index,
            $value,
        );

        $this->ffi->destroyValue($this->ffi->addr($value));

        if ($status === $this->ffi->error()) {
            $error = $this->ffi->prepareError($this->preparedStatement);
            throw new BindValueException("Couldn't bind parameter '{$parameter}' to prepared statement {$this->query}. Error: {$error}");
        }
    }

    /**
     * @throws BindValueException
     */
    private function getParameterIndex(int|string $parameter): int
    {
        if (is_int($parameter)) {
            return $parameter;
        }

        $index = $this->ffi->new('idx_t');
        if ($this->ffi->bindParameterIndex($this->preparedStatement, $index, $parameter) === $this->ffi->success()) {
            return $index->cdata;
        }

        throw new BindValueException("Couldn't bind parameter '{$parameter}' to prepared statement.");
    }

    /**
     * @throws PreparedStatementExecuteException
     */
    public function execute(): ResultSet
    {
        $queryResult = $this->ffi->new('duckdb_result');

        $result = $this->ffi->executePrepared($this->preparedStatement, $this->ffi->addr($queryResult));

        if ($result === $this->ffi->error()) {
            $error = $this->ffi->resultError($this->ffi->addr($queryResult)) ?? 'Unknown error';
            $this->ffi->destroyResult($this->ffi->addr($queryResult));
            throw new PreparedStatementExecuteException($error);
        }

        return new ResultSet($this->ffi, $queryResult);
    }

    public function pendingExecute(): PendingResult
    {
        $pendingResult = $this->ffi->new('duckdb_pending_result');
        $this->ffi->pendingPrepared($this->preparedStatement, $this->ffi->addr($pendingResult));

        return new PendingResult($this->ffi, $pendingResult);
    }

    public function __destruct()
    {
        $this->ffi->destroyPrepared($this->ffi->addr($this->preparedStatement));
    }
}
