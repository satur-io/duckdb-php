<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use FFI;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\Exception\NotSupportedException;
use Saturio\DuckDB\FFI\CData as DuckDBCData;

class DuckDB
{
    private static ?\FFI $ffi = null;

    /**
     * @throws NotSupportedException
     * @throws MissedLibraryException
     */
    public function __construct()
    {
        if (is_null(self::$ffi)) {
            try {
                self::$ffi = \FFI::scope('DUCKDB');
            } catch (FFI\Exception) {
                $headerPath = FindLibrary::headerPath();
                $libPath = FindLibrary::libPath();

                if (!file_exists($headerPath)) {
                    throw new MissedLibraryException("Could not load library header file '$headerPath'.");
                }

                if (!file_exists($libPath)) {
                    throw new MissedLibraryException("Could not load library '$libPath'.");
                }

                self::$ffi = \FFI::cdef(
                    file_get_contents($headerPath),
                    $libPath,
                );
            }
        }
    }

    public function new(string $name, bool $owned = true): ?CDataInterface
    {
        $new = self::$ffi->new($name, $owned);
        if (null === $new) {
            return null;
        }

        return new DuckDBCData($new);
    }

    public function addr(CDataInterface $name): ?CDataInterface
    {
        return new DuckDBCData(\FFI::addr($name->cdata));
    }

    public function free(CDataInterface $pointer): void
    {
        \FFI::free($pointer->cdata);
    }

    public function error(): int
    {
        return self::$ffi->DuckDBError;
    }

    public function success(): int
    {
        return self::$ffi->DuckDBSuccess;
    }

    public function open(?string $path, CDataInterface $database): int
    {
        return self::$ffi->duckdb_open($path, $database->cdata);
    }

    public function close(CDataInterface $database): ?int
    {
        return self::$ffi->duckdb_close($database->cdata);
    }

    public function connect(CDataInterface $database, CDataInterface $connection): int
    {
        return self::$ffi->duckdb_connect($database->cdata, $connection->cdata);
    }

    public function disconnect(CDataInterface $database): ?int
    {
        return self::$ffi->duckdb_disconnect($database->cdata);
    }

    public function resultError(CDataInterface $result): ?string
    {
        return self::$ffi->duckdb_result_error($result->cdata);
    }

    public function columnName(CDataInterface $result, $index): ?string
    {
        return self::$ffi->duckdb_column_name($result->cdata, $index);
    }

    public function fetchChunk(CDataInterface $result): ?CDataInterface
    {
        $chunk = self::$ffi->duckdb_fetch_chunk($result->cdata);
        if (null === $chunk) {
            return null;
        }

        return new DuckDBCData($chunk);
    }

    public function fetchChunkToCDataInterface(CDataInterface $result, CDataInterface $chunk): bool
    {
        self::$ffi->duckdb_data_chunk_reset($chunk->cdata);
        $chunkCData = self::$ffi->duckdb_fetch_chunk($result->cdata);
        if (null === $chunkCData) {
            return false;
        }

        $chunk->cdata = $chunkCData;

        return true;
    }

    public function dataChunkGetSize(CDataInterface $dataChunk): int
    {
        return self::$ffi->duckdb_data_chunk_get_size($dataChunk->cdata);
    }

    public function dataChunkGetColumnCount(CDataInterface $dataChunk): int
    {
        return self::$ffi->duckdb_data_chunk_get_column_count($dataChunk->cdata);
    }

    public function dataChunkGetVector(CDataInterface $dataChunk, int $columnIndex): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_data_chunk_get_vector($dataChunk->cdata, $columnIndex));
    }

    public function dataChunkGetVectorToCDataInterface(CDataInterface $dataChunk, int $columnIndex, CDataInterface $vector): void
    {
        $vector->cdata = self::$ffi->duckdb_data_chunk_get_vector($dataChunk->cdata, $columnIndex);
    }

    public function vectorGetData(CDataInterface $column): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_vector_get_data($column->cdata));
    }

    public function vectorGetValidity(CDataInterface $column): ?CDataInterface
    {
        $validity = self::$ffi->duckdb_vector_get_validity($column->cdata);
        if (null === $validity) {
            return null;
        }

        return new DuckDBCData($validity);
    }

    public function query(CDataInterface $connection, string $query, CDataInterface $result): int
    {
        return self::$ffi->duckdb_query($connection->cdata, $query, $result->cdata);
    }

    public function validityRowIsValid(CDataInterface $validity, int $row): bool
    {
        return self::$ffi->duckdb_validity_row_is_valid($validity->cdata, $row);
    }

    public function cast(string $type, CDataInterface $data): CDataInterface
    {
        $data->cdata = self::$ffi->cast($type, $data->cdata);

        return $data;
    }

    public function vectorGetColumnType(CDataInterface $column): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_vector_get_column_type($column->cdata));
    }

    public function getTypeId(CDataInterface $logicalType): int
    {
        return self::$ffi->duckdb_get_type_id($logicalType->cdata);
    }

    public function destroyResult(CDataInterface $result): void
    {
        self::$ffi->duckdb_destroy_result($result->cdata);
    }

    public function stringIsInlined(CDataInterface $data): bool
    {
        return self::$ffi->duckdb_string_is_inlined($data->cdata);
    }

    public function destroyDataChunk(CDataInterface $dataChunk): ?int
    {
        return self::$ffi->duckdb_destroy_data_chunk($dataChunk->cdata);
    }

    public function decimalToDouble(CDataInterface $decimal): float
    {
        return self::$ffi->duckdb_decimal_to_double($decimal->cdata);
    }

    public function doubleToHugeint(int $decimal): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_double_to_hugeint($decimal));
    }

    public function decimalScale(CDataInterface $logicalType): int
    {
        return self::$ffi->duckdb_decimal_scale($logicalType->cdata);
    }

    public function decimalWidth(CDataInterface $logicalType): int
    {
        return self::$ffi->duckdb_decimal_width($logicalType->cdata);
    }

    public function prepare(CDataInterface $connection, string $query, CDataInterface $result): int
    {
        return self::$ffi->duckdb_prepare($connection->cdata, $query, $result->cdata);
    }

    public function bindValue(CDataInterface $preparedStatement, int $parameterIndex, mixed $value): int
    {
        $value = is_a($value, DuckDBCData::class) ? $value->cdata : $value;

        return self::$ffi->duckdb_bind_value($preparedStatement->cdata, $parameterIndex, $value);
    }

    public function prepareError(CDataInterface $preparedStatement): ?string
    {
        return self::$ffi->duckdb_prepare_error($preparedStatement->cdata);
    }

    public function executePrepared(CDataInterface $preparedStatement, CDataInterface $result): int
    {
        return self::$ffi->duckdb_execute_prepared($preparedStatement->cdata, $result->cdata);
    }

    public function destroyPrepared(CDataInterface $preparedStatement): void
    {
        self::$ffi->duckdb_destroy_prepare($preparedStatement->cdata);
    }

    public function createDuckdb_string_t(string $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_varchar($value));
    }

    public function createBool(bool $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_bool($value));
    }

    public function createTimeTzValue(CDataInterface $timeTz): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_time_tz_value($timeTz->cdata));
    }

    public function createInt8_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_int8($value));
    }

    public function createUint8_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_uint8($value));
    }

    public function createInt16_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_int16($value));
    }

    public function createUInt16_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_uint16($value));
    }

    public function createInt32_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_int32($value));
    }

    public function createUint32_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_uint32($value));
    }

    public function createInt64_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_int32($value));
    }

    public function createUint64_t(int $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_int32($value));
    }

    public function createDouble(float $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_double($value));
    }

    public function createFloat(float $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_float($value));
    }

    public function toDate(CDataInterface $dateStruct): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_to_date($dateStruct->cdata));
    }

    public function fromDate(CDataInterface $date): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_from_date($date->cdata));
    }

    public function createDate(CDataInterface $date): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_date($date->cdata));
    }

    public function toTime(CDataInterface $timeStruct): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_to_time($timeStruct->cdata));
    }

    public function fromTime(CDataInterface $time): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_from_time($time->cdata));
    }

    public function fromTimeTz(CDataInterface $time): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_from_time_tz($time->cdata));
    }

    public function createTime(CDataInterface $time): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_time($time->cdata));
    }

    public function toTimestamp(CDataInterface $timestampStruct): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_to_timestamp($timestampStruct->cdata));
    }

    public function fromTimestamp(CDataInterface $timestamp): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_from_timestamp($timestamp->cdata));
    }

    public function createTimestamp(CDataInterface $time): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_create_timestamp($time->cdata));
    }

    public function getValueType(CDataInterface $value): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_get_value_type($value->cdata));
    }

    public function structVectorGetChild(CDataInterface $struct, int $index): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_struct_vector_get_child($struct->cdata, $index));
    }

    public function listVectorGetChild(CDataInterface $list): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_list_vector_get_child($list->cdata));
    }

    public function structTypeChildCount(CDataInterface $logicalType): int
    {
        return self::$ffi->duckdb_struct_type_child_count($logicalType->cdata);
    }

    public function structTypeChildName(CDataInterface $logicalType, int $index): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_struct_type_child_name($logicalType->cdata, $index));
    }

    public function decimalInternalType(CDataInterface $logicalType): int
    {
        return self::$ffi->duckdb_decimal_internal_type($logicalType->cdata);
    }

    public function valueDecimal(CDataInterface $result, int $col, int $row): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_value_decimal($result->cdata, $col, $row));
    }

    public function getMapSize(CDataInterface $map): int
    {
        return self::$ffi->duckdb_get_map_size($map->cdata);
    }

    public function getMapKey(CDataInterface $map, int $index): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_get_map_key($map->cdata, $index));
    }

    public function getMapValue(CDataInterface $map, int $index): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_get_map_value($map->cdata, $index));
    }

    public function arrayVectorGetChild(CDataInterface $array): CDataInterface
    {
        return new DuckDBCData(self::$ffi->duckdb_array_vector_get_child($array->cdata));
    }

    public function arraySize(CDataInterface $array): int
    {
        return self::$ffi->duckdb_array_type_array_size($array->cdata);
    }

    public function columnCount(CDataInterface $result): int
    {
        return self::$ffi->duckdb_column_count($result->cdata);
    }

    public function string(CDataInterface $string, ?int $length = null): string
    {
        return \FFI::string($string->cdata, $length);
    }

    public function destroyLogicalType(CDataInterface $logicalType): void
    {
        self::$ffi->duckdb_destroy_logical_type($logicalType->cdata);
    }

    public function getVarchar(CDataInterface $duckdbValue): string
    {
        return \FFI::string(self::$ffi->duckdb_get_varchar($duckdbValue->cdata));
    }
}
