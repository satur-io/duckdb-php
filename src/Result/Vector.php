<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use DateMalformedStringException;
use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;
use Saturio\DuckDB\Type\Converter\NumericConverter;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Math\MathLib;
use Saturio\DuckDB\Type\Type;
use Saturio\DuckDB\Type\TypeC;

class Vector
{
    use ValidityTrait;
    private TypeC $type;
    private NativeCData $typedData;
    private NativeCData $logicalType;
    private ?NativeCData $validity;
    public ?NestedTypeVector $nestedTypeVector = null;
    private NumericConverter $numericConverter;
    private TypeConverter $typeConverter;

    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly NativeCData $vector,
        public readonly int $rows,
        public readonly ?string $name = null,
    ) {
        $this->type = $this->getColumnType();

        $this->nestedTypeVector = match ($this->type) {
            TypeC::DUCKDB_TYPE_STRUCT => new StructVector($this->ffi, $this->vector, $this->rows, $this->logicalType),
            TypeC::DUCKDB_TYPE_LIST => new ListVector($this->ffi, $this->vector, $this->rows),
            TypeC::DUCKDB_TYPE_MAP => new MapVector($this->ffi, $this->vector, $this->rows),
            TypeC::DUCKDB_TYPE_ARRAY => new ArrayVector($this->ffi, $this->vector, $this->rows, $this->logicalType),
            TypeC::DUCKDB_TYPE_UNION => new UnionVector($this->ffi, $this->vector, $this->rows, $this->logicalType),
            default => null,
        };

        if ($this->isNestedType()) {
            return;
        }

        $this->typedData = match ($this->type) {
            TypeC::DUCKDB_TYPE_DECIMAL => $this->cast(TypeC::{Type::from($this->ffi->decimalInternalType($this->logicalType))->name}),
            TypeC::DUCKDB_TYPE_TIMESTAMP_TZ => $this->cast(TypeC::DUCKDB_TYPE_TIMESTAMP),
            TypeC::DUCKDB_TYPE_UUID => $this->cast(TypeC::DUCKDB_TYPE_UHUGEINT),
            TypeC::DUCKDB_TYPE_ENUM => $this->cast(TypeC::{Type::from($this->ffi->enumInternalType($this->logicalType))->name}),
            TypeC::DUCKDB_TYPE_BLOB, TypeC::DUCKDB_TYPE_BIT, TypeC::DUCKDB_TYPE_VARINT => $this->cast(TypeC::DUCKDB_TYPE_VARCHAR),
            default => $this->cast($this->type),
        };

        $this->validity = $this->getValidity();

        if (TypeC::DUCKDB_TYPE_DECIMAL === $this->type) {
            $this->numericConverter = new NumericConverter($this->ffi);
        } else {
            $this->typeConverter = new TypeConverter($this->ffi, MathLib::create());
        }
    }

    /**
     * @throws BigNumbersNotSupportedException
     * @throws DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getDataGenerator(): iterable
    {
        if ($this->isNestedType()) {
            for ($rowIndex = 0; $rowIndex < $this->rows; ++$rowIndex) {
                yield $this->nestedTypeVector->getChildren($rowIndex);
            }
        } else {
            for ($rowIndex = 0; $rowIndex < $this->rows; ++$rowIndex) {
                yield $this->getTypedData($rowIndex);
            }
        }
    }

    /**
     * @throws DateMalformedStringException
     * @throws BigNumbersNotSupportedException
     * @throws InvalidTimeException
     */
    public function getBatchRows(): array
    {
        $rows = [];
        for ($i = 0; $i < $this->rows; ++$i) {
            $rows[] = $this->getTypedData($i);
        }

        return $rows;
    }

    public function getValidity(): ?NativeCData
    {
        $validity = $this->ffi->vectorGetValidity($this->vector);

        if (null === $validity) {
            return null;
        }

        return $this->ffi->cast(
            'uint64_t *',
            $validity,
        );
    }

    private function getColumnType(): TypeC
    {
        $this->logicalType = $this->ffi->vectorGetColumnType($this->vector);

        return TypeC::{Type::from(
            $this->ffi->getTypeId(
                $this->logicalType,
            )
        )->name};
    }

    private function cast(TypeC $type): NativeCData
    {
        return $this->ffi->cast(
            "{$type->value} *",
            $this->ffi->vectorGetData($this->vector),
        );
    }

    /**
     * @throws InvalidTimeException
     * @throws DateMalformedStringException
     * @throws BigNumbersNotSupportedException
     */
    public function getTypedData(int $rowIndex): mixed
    {
        if (!$this->rowIsValid($this->validity, $rowIndex)) {
            return null;
        }

        $data = $this->typedData[$rowIndex];

        return match ($this->type) {
            TypeC::DUCKDB_TYPE_VARCHAR => $this->typeConverter->getVarChar($data),
            TypeC::DUCKDB_TYPE_DECIMAL => $this->numericConverter->getFloatFromDecimal($data, $this->logicalType),
            TypeC::DUCKDB_TYPE_DATE => $this->typeConverter->getDateFromDuckDBDate($data),
            TypeC::DUCKDB_TYPE_TIME => $this->typeConverter->getTimeFromDuckDBTime($data),
            TypeC::DUCKDB_TYPE_TIME_TZ => $this->typeConverter->getTimeFromDuckDBTimeTz($data),
            TypeC::DUCKDB_TYPE_TIMESTAMP => $this->typeConverter->getTimestampFromDuckDBTimestamp($data),
            TypeC::DUCKDB_TYPE_TIMESTAMP_MS => $this->typeConverter->getTimestampFromDuckDBTimestampMs($data),
            TypeC::DUCKDB_TYPE_TIMESTAMP_S => $this->typeConverter->getTimestampFromDuckDBTimestampS($data),
            TypeC::DUCKDB_TYPE_TIMESTAMP_NS => $this->typeConverter->getTimestampFromDuckDBTimestampNs($data),
            TypeC::DUCKDB_TYPE_TIMESTAMP_TZ => $this->typeConverter->getTimestampFromDuckDBTimestampTz($data),
            TypeC::DUCKDB_TYPE_INTERVAL => $this->typeConverter->getIntervalFromDuckDBInterval($data),
            TypeC::DUCKDB_TYPE_UBIGINT => $this->typeConverter->getBigIntFromDuckDBBigInt($data, true),
            TypeC::DUCKDB_TYPE_HUGEINT => $this->typeConverter->getHugeIntFromDuckDBHugeInt($data, unsigned: false),
            TypeC::DUCKDB_TYPE_UHUGEINT => $this->typeConverter->getHugeIntFromDuckDBHugeInt($data, unsigned: true),
            TypeC::DUCKDB_TYPE_UUID => $this->typeConverter->getUUIDFromDuckDBHugeInt($data),
            TypeC::DUCKDB_TYPE_ENUM => $this->typeConverter->getStringFromEnum($this->logicalType, $data),
            TypeC::DUCKDB_TYPE_BLOB => $this->typeConverter->getBlobFromBlob($data),
            TypeC::DUCKDB_TYPE_BIT => $this->typeConverter->getStringFromDuckDBBit($data),
            TypeC::DUCKDB_TYPE_VARINT => $this->typeConverter->getStringFromDuckDBVarInt($data),
            default => $data,
        };
    }

    public function isNestedType(): bool
    {
        return null !== $this->nestedTypeVector;
    }

    public function __destruct()
    {
        $this->ffi->destroyLogicalType($this->ffi->addr($this->logicalType));
    }
}
