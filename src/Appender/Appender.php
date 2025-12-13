<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Appender;

use DateInvalidTimeZoneException;
use DateMalformedStringException;
use Saturio\DuckDB\Exception\AppenderEndRowException;
use Saturio\DuckDB\Exception\AppenderFlushException;
use Saturio\DuckDB\Exception\AppendValueException;
use Saturio\DuckDB\Exception\ErrorCreatingNewAppender;
use Saturio\DuckDB\Exception\UnexpectedTypeException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\Type;
use function PHPUnit\Framework\matches;

class Appender
{
    private NativeCData $appender;
    private TypeConverter $converter;

    private int $currentColumn = 0;

    private array $columnTypes = [];

    private function __construct(
        private readonly FFIDuckDB $ffi,
    ) {
        $this->converter = new TypeConverter($this->ffi, MathLib::create());
    }

    /**
     * @throws ErrorCreatingNewAppender
     */
    public static function create(
        FFIDuckDB $ffi,
        NativeCData $connection,
        string $table,
        ?string $schema = null,
        ?string $catalog = null,
    ): self {
        $appender = new self($ffi);
        $appender->appender = $ffi->new('duckdb_appender');

        $status = $ffi->createAppender(
            $connection,
            $catalog,
            $schema,
            $table,
            $ffi->addr($appender->appender),
        );

        if ($status === $ffi->error()) {
            throw new ErrorCreatingNewAppender('The appender cannot be created. '.$ffi->appenderError($appender->appender));
        }

        return $appender;
    }

    /**
     * @throws UnsupportedTypeException
     * @throws AppendValueException|DateMalformedStringException|UnexpectedTypeException|DateInvalidTimeZoneException
     */
    public function append(mixed $value, ?Type $type = null): void
    {
        $appenderColumnType = fn()=> $this->ffi->getTypeId($this->ffi->appenderColumnType($this->appender, $this->currentColumn));
        $type = is_null($value) ? Type::DUCKDB_TYPE_SQLNULL : $type ?? Type::from($appenderColumnType());
        $nativeValue = $this->converter->getDuckDBValue($value, $type);
        $status = $this->ffi->appendValue(
            $this->appender,
            $nativeValue,
        );

        $this->ffi->destroyValue($this->ffi->addr($nativeValue));

        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }


    /**
     * @throws AppendValueException
     */
    public function quickAppend(mixed $value, ?Type $type = null, bool $inferType = false): void
    {
        if ($inferType) {
            $type ??= match(gettype($value)) {
                'NULL' => Type::DUCKDB_TYPE_SQLNULL,
                'string' => Type::DUCKDB_TYPE_VARCHAR,
                'integer' => Type::DUCKDB_TYPE_BIGINT,
                'boolean' => Type::DUCKDB_TYPE_BOOLEAN,
                'double' => Type::DUCKDB_TYPE_FLOAT,
                default => null,
            };
        }

        $status = match ($type) {
            Type::DUCKDB_TYPE_VARCHAR => $this->ffi->appendVarchar(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_SQLNULL => $this->ffi->appendNull(
                $this->appender,
            ),
            Type::DUCKDB_TYPE_TINYINT,
            Type::DUCKDB_TYPE_SMALLINT,
            Type::DUCKDB_TYPE_INTEGER,
            Type::DUCKDB_TYPE_BIGINT,
            Type::DUCKDB_TYPE_UTINYINT,
            Type::DUCKDB_TYPE_USMALLINT,
            Type::DUCKDB_TYPE_UINTEGER => $this->ffi->appendInt64(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_DOUBLE,
            Type::DUCKDB_TYPE_FLOAT => $this->ffi->appendDouble(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_BOOLEAN => $this->ffi->appendBool(
                $this->appender,
                $value,
            ),
            default => $this->ffi->appendVarchar(
                $this->appender,
                (string) $value,
            ),
        };

        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }

    /**
     * @throws AppendValueException
     */
    public function quickAppendV2(mixed $value, bool $inferType = false): void
    {
        $type = $inferType ? match(gettype($value)) {
            'NULL' => Type::DUCKDB_TYPE_SQLNULL,
            'string' => Type::DUCKDB_TYPE_VARCHAR,
            'integer' => Type::DUCKDB_TYPE_BIGINT,
            'boolean' => Type::DUCKDB_TYPE_BOOLEAN,
            'double' => Type::DUCKDB_TYPE_FLOAT,
            default => null,
        } : null;

        $status = match ($type) {
            Type::DUCKDB_TYPE_VARCHAR => $this->ffi->appendVarchar(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_SQLNULL => $this->ffi->appendNull(
                $this->appender,
            ),
            Type::DUCKDB_TYPE_BIGINT => $this->ffi->appendInt64(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_FLOAT => $this->ffi->appendDouble(
                $this->appender,
                $value,
            ),
            Type::DUCKDB_TYPE_BOOLEAN => $this->ffi->appendBool(
                $this->appender,
                $value,
            ),
            default => $this->ffi->appendVarchar(
                $this->appender,
                (string) $value,
            ),
        };

        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }


    public function appendDefault(): void
    {
        $status = $this->ffi->appendDefault(
            $this->appender
        );

        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append default. Error: {$error}");
        }

        ++$this->currentColumn;
    }

    public function appendVarchar(string $value, ?int $length = null): void
    {
        if ($length === null) {
            $status = $this->ffi->appendVarchar(
                $this->appender,
                $value,
            );
        } else {
            $status = $this->ffi->appendVarcharLength(
                $this->appender,
                $value,
                $length,
            );
        }


        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }

    public function appendInt(int $value): void
    {
        $this->appendInt64($value);
    }

    public function appendInt64(int $value): void
    {
        $status = $this->ffi->appendInt64(
            $this->appender,
            $value,
        );


        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }

    public function appendInt32(int $value): void
    {
        $status = $this->ffi->appendInt32(
            $this->appender,
            $value,
        );


        if ($status === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppendValueException("Couldn't append {$value}. Error: {$error}");
        }

        ++$this->currentColumn;
    }

    /**
     * @throws AppenderEndRowException
     */
    public function endRow(): void
    {
        if ($this->ffi->endRow($this->appender) === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppenderEndRowException("Couldn't end the row.{$error}");
        }

        $this->currentColumn = 0;
    }

    /**
     * @throws AppenderFlushException
     */
    public function flush(): void
    {
        if ($this->ffi->flush($this->appender) === $this->ffi->error()) {
            $error = $this->ffi->appenderError($this->appender);
            throw new AppenderFlushException("Couldn't flush.{$error}");
        }
    }

    public function __destruct()
    {
        $this->ffi->destroyAppender($this->ffi->addr($this->appender));
    }
}
