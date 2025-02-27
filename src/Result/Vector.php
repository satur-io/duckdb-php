<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Result;

use Saturio\DuckDB\Exception\BigNumbersNotSupportedException;
use Saturio\DuckDB\Exception\InvalidTimeException;
use Saturio\DuckDB\Exception\UnsupportedTypeException;
use Saturio\DuckDB\FFI\CDataInterface;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\Type\Converter\NumericConverter;
use Saturio\DuckDB\Type\Converter\TypeConverter;
use Saturio\DuckDB\Type\Type;
use Saturio\DuckDB\Type\TypeC;

class Vector
{
    use ValidityTrait;
    private TypeC $type;
    private CDataInterface $typedData;
    private CDataInterface $logicalType;
    private ?CDataInterface $validity;

    public ?NestedTypeVector $nestedTypeVector = null;
    private CDataInterface $currentValue;
    private NumericConverter $numericConverter;

    public function __construct(
        private readonly FFIDuckDB $ffi,
        private readonly CDataInterface $vector,
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
            TypeC::DUCKDB_TYPE_UUID => $this->cast(TypeC::DUCKDB_TYPE_HUGEINT),
            default => $this->cast($this->type),
        };

        $this->validity = $this->getValidity();
        $this->currentValue = $ffi->new(TypeC::DUCKDB_TYPE_BOOLEAN->value);

        if (TypeC::DUCKDB_TYPE_DECIMAL === $this->type) {
            $this->numericConverter = new NumericConverter($this->ffi);
        }
    }

    /**
     * @throws BigNumbersNotSupportedException
     * @throws \DateMalformedStringException
     * @throws InvalidTimeException
     */
    public function getDataGenerator(): \Generator
    {
        for ($rowIndex = 0; $rowIndex < $this->rows; ++$rowIndex) {
            if ($this->isNestedType()) {
                yield $this->nestedTypeVector->getChildren($rowIndex);
                continue;
            }

            yield $this->getTypedData($rowIndex);
        }
    }

    public function getValidity(): ?CDataInterface
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

    private function cast(TypeC $type): CDataInterface
    {
        return $this->ffi->cast(
            "{$type->value} *",
            $this->ffi->vectorGetData($this->vector),
        );
    }

    /**
     * @throws InvalidTimeException
     * @throws \DateMalformedStringException
     * @throws BigNumbersNotSupportedException
     * @throws UnsupportedTypeException
     */
    public function getTypedData(int $rowIndex): mixed
    {
        if (!$this->rowIsValid($this->validity, $rowIndex)) {
            return null;
        }

        $data = $this->typedData->get($rowIndex);

        if (!is_scalar($data)) {
            $this->currentValue->cdata = $data;
        }

        return match ($this->type) {
            TypeC::DUCKDB_TYPE_VARCHAR => TypeConverter::getVarChar($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_DECIMAL => $this->numericConverter->getFloatFromDecimal(is_scalar($data) ? $data : $this->currentValue, $this->logicalType),
            TypeC::DUCKDB_TYPE_DATE => TypeConverter::getDateFromDuckDBDate($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_TIME => TypeConverter::getTimeFromDuckDBTime($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_TIME_TZ => TypeConverter::getTimeFromDuckDBTimeTz($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_TIMESTAMP => TypeConverter::getTimestampFromDuckDBTimestamp($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_TIMESTAMP_MS => TypeConverter::getTimestampFromDuckDBTimestampMs($this->currentValue),
            TypeC::DUCKDB_TYPE_TIMESTAMP_S => TypeConverter::getTimestampFromDuckDBTimestampS($this->currentValue),
            TypeC::DUCKDB_TYPE_TIMESTAMP_NS => TypeConverter::getTimestampFromDuckDBTimestampNs($this->currentValue),
            TypeC::DUCKDB_TYPE_TIMESTAMP_TZ => TypeConverter::getTimestampFromDuckDBTimestampTz($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_INTERVAL => TypeConverter::getIntervalFromDuckDBInterval($this->currentValue),
            TypeC::DUCKDB_TYPE_UBIGINT => TypeConverter::getUBigIntFromDuckDBUBigInt($data),
            TypeC::DUCKDB_TYPE_HUGEINT, TypeC::DUCKDB_TYPE_UHUGEINT => TypeConverter::getHugeIntFromDuckDBHugeInt($this->currentValue),
            TypeC::DUCKDB_TYPE_UUID => TypeConverter::getUUIDFromDuckDBHugeInt($this->currentValue),
            TypeC::DUCKDB_TYPE_BIT => throw new UnsupportedTypeException('Type BIT/BITSTRING is not supported by duckdb-php yet'), // @todo - Check why does not work TypeConverter::getBitDuckDBBit($this->currentValue, $this->ffi),
            TypeC::DUCKDB_TYPE_BLOB => throw new UnsupportedTypeException('Type BLOB is not supported by duckdb-php yet'), // @todo - Check why does not work TypeConverter::getBlobDuckDBlob($this->currentValue, $this->ffi),
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
