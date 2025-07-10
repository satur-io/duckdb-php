<?php

declare(strict_types=1);

namespace Saturio\DuckDB\FFI;

use FFI;
use FFI\CType;
use Saturio\DuckDB\DB\Configuration;
use Saturio\DuckDB\Exception\MissedLibraryException;
use Saturio\DuckDB\Exception\NotSupportedException;
use Saturio\DuckDB\Native\FFI\CData as NativeCData;

class DuckDB
{
    private static ?FFI $ffi = null;

    /**
     * @throws NotSupportedException
     * @throws MissedLibraryException
     */
    public function __construct()
    {
        if (is_null(self::$ffi)) {
            try {
                self::$ffi = FFI::scope('DUCKDB');
            } catch (FFI\Exception) {
                $headerPath = FindLibrary::headerPath();
                $libPath = FindLibrary::libPath();

                if (!file_exists($headerPath)) {
                    throw new MissedLibraryException("Could not load library header file '$headerPath'.");
                }

                if (!file_exists($libPath)) {
                    throw new MissedLibraryException("Could not load library '$libPath'.");
                }

                self::$ffi = FFI::cdef(
                    file_get_contents($headerPath),
                    $libPath,
                );
            }
        }
    }

    public function new(string|CType $name, bool $owned = true): ?NativeCData
    {
        return self::$ffi->new($name, $owned);
    }

    public function addr(NativeCData $name): ?NativeCData
    {
        return FFI::addr($name);
    }

    public function free(NativeCData $pointer): void
    {
        FFI::free($pointer);
    }

    public function duckDBFree(NativeCData $pointer): void
    {
        self::$ffi->duckdb_free($pointer);
    }

    public function type(string $type): CType
    {
        return self::$ffi->type($type);
    }

    public function error(): int
    {
        return self::$ffi->DuckDBError;
    }

    public function success(): int
    {
        return self::$ffi->DuckDBSuccess;
    }

    public function open(?string $path, NativeCData $database): int
    {
        return self::$ffi->duckdb_open($path, $database);
    }

    public function openExt(?string $path, NativeCData $database, NativeCData $configuration, ?string &$error): int
    {
        $errorCData = $this->cast('char  *', $this->new('char[1]'));
        $status = self::$ffi->duckdb_open_ext($path, $database, $configuration, FFI::addr($errorCData));
        $error = self::$ffi::string($errorCData);

        return $status;
    }

    public function close(NativeCData $database): ?int
    {
        return self::$ffi->duckdb_close($database);
    }

    public function connect(NativeCData $database, NativeCData $connection): int
    {
        return self::$ffi->duckdb_connect($database, $connection);
    }

    public function disconnect(NativeCData $database): ?int
    {
        return self::$ffi->duckdb_disconnect($database);
    }

    public function resultError(NativeCData $result): ?string
    {
        return self::$ffi->duckdb_result_error($result);
    }

    public function columnName(NativeCData $result, int $index): ?string
    {
        return self::$ffi->duckdb_column_name($result, $index);
    }

    public function fetchChunk(NativeCData $result): ?NativeCData
    {
        return self::$ffi->duckdb_fetch_chunk($result);
    }

    public function fetchChunkToNativeCData(NativeCData $result, NativeCData &$chunk): bool
    {
        self::$ffi->duckdb_data_chunk_reset($chunk);
        $newChunk = self::$ffi->duckdb_fetch_chunk($result);
        if (null === $newChunk) {
            return false;
        }
        $chunk = $newChunk;

        return true;
    }

    public function dataChunkGetSize(NativeCData $dataChunk): int
    {
        return self::$ffi->duckdb_data_chunk_get_size($dataChunk);
    }

    public function dataChunkGetColumnCount(NativeCData $dataChunk): int
    {
        return self::$ffi->duckdb_data_chunk_get_column_count($dataChunk);
    }

    public function dataChunkGetVector(NativeCData $dataChunk, int $columnIndex): NativeCData
    {
        return self::$ffi->duckdb_data_chunk_get_vector($dataChunk, $columnIndex);
    }

    public function dataChunkGetVectorToNativeCData(NativeCData $dataChunk, int $columnIndex, NativeCData &$vector): void
    {
        $vector = self::$ffi->duckdb_data_chunk_get_vector($dataChunk, $columnIndex);
    }

    public function vectorGetData(NativeCData $column): NativeCData
    {
        return self::$ffi->duckdb_vector_get_data($column);
    }

    public function vectorGetValidity(NativeCData $column): ?NativeCData
    {
        return self::$ffi->duckdb_vector_get_validity($column);
    }

    public function query(NativeCData $connection, string $query, NativeCData $result): int
    {
        return self::$ffi->duckdb_query($connection, $query, $result);
    }

    public function validityRowIsValid(NativeCData $validity, int $row): bool
    {
        return self::$ffi->duckdb_validity_row_is_valid($validity, $row);
    }

    public function cast(string $type, NativeCData $data): NativeCData
    {
        return self::$ffi->cast($type, $data);
    }

    public function vectorGetColumnType(NativeCData $column): NativeCData
    {
        return self::$ffi->duckdb_vector_get_column_type($column);
    }

    public function getTypeId(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_get_type_id($logicalType);
    }

    public function destroyResult(NativeCData $result): void
    {
        self::$ffi->duckdb_destroy_result($result);
    }

    public function stringIsInlined(NativeCData $data): bool
    {
        return self::$ffi->duckdb_string_is_inlined($data);
    }

    public function destroyDataChunk(NativeCData $dataChunk): ?int
    {
        return self::$ffi->duckdb_destroy_data_chunk($dataChunk);
    }

    public function decimalToDouble(NativeCData $decimal): float
    {
        return self::$ffi->duckdb_decimal_to_double($decimal);
    }

    public function doubleToHugeint(int $decimal): NativeCData
    {
        return self::$ffi->duckdb_double_to_hugeint($decimal);
    }

    public function decimalScale(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_decimal_scale($logicalType);
    }

    public function decimalWidth(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_decimal_width($logicalType);
    }

    public function prepare(NativeCData $connection, string $query, NativeCData $result): int
    {
        return self::$ffi->duckdb_prepare($connection, $query, $result);
    }

    public function bindValue(NativeCData $preparedStatement, int $parameterIndex, mixed $value): int
    {
        return self::$ffi->duckdb_bind_value($preparedStatement, $parameterIndex, $value);
    }

    public function prepareError(NativeCData $preparedStatement): ?string
    {
        return self::$ffi->duckdb_prepare_error($preparedStatement);
    }

    public function executePrepared(NativeCData $preparedStatement, NativeCData $result): int
    {
        return self::$ffi->duckdb_execute_prepared($preparedStatement, $result);
    }

    public function destroyPrepared(NativeCData $preparedStatement): void
    {
        self::$ffi->duckdb_destroy_prepare($preparedStatement);
    }

    public function createDuckdb_string_t(string|NativeCData $value): NativeCData
    {
        return self::$ffi->duckdb_create_varchar($value);
    }

    public function createBool(bool $value): NativeCData
    {
        return self::$ffi->duckdb_create_bool($value);
    }

    public function createTimeTzValue(NativeCData $timeTz): NativeCData
    {
        return self::$ffi->duckdb_create_time_tz_value($timeTz);
    }

    public function createBit(NativeCData $bit): NativeCData
    {
        return self::$ffi->duckdb_create_bit($bit);
    }

    public function createBlob(array|NativeCData $blob, int $size): NativeCData
    {
        return self::$ffi->duckdb_create_blob($blob, $size);
    }

    public function createInt8_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_int8($value);
    }

    public function createUint8_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_uint8($value);
    }

    public function createInt16_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_int16($value);
    }

    public function createUInt16_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_uint16($value);
    }

    public function createInt32_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_int32($value);
    }

    public function createUint32_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_uint32($value);
    }

    public function createInt64_t(int $value): NativeCData
    {
        return self::$ffi->duckdb_create_int32($value);
    }

    public function createUint64_t(int|string $value): NativeCData
    {
        return self::$ffi->duckdb_create_int32($value);
    }

    public function createDouble(float $value): NativeCData
    {
        return self::$ffi->duckdb_create_double($value);
    }

    public function createFloat(float $value): NativeCData
    {
        return self::$ffi->duckdb_create_float($value);
    }

    public function toDate(NativeCData $dateStruct): NativeCData
    {
        return self::$ffi->duckdb_to_date($dateStruct);
    }

    public function fromDate(NativeCData $date): NativeCData
    {
        return self::$ffi->duckdb_from_date($date);
    }

    public function createDate(NativeCData $date): NativeCData
    {
        return self::$ffi->duckdb_create_date($date);
    }

    public function toTime(NativeCData $timeStruct): NativeCData
    {
        return self::$ffi->duckdb_to_time($timeStruct);
    }

    public function fromTime(NativeCData $time): NativeCData
    {
        return self::$ffi->duckdb_from_time($time);
    }

    public function fromTimeTz(NativeCData $time): NativeCData
    {
        return self::$ffi->duckdb_from_time_tz($time);
    }

    public function createTime(NativeCData $time): NativeCData
    {
        return self::$ffi->duckdb_create_time($time);
    }

    public function toTimestamp(NativeCData $timestampStruct): NativeCData
    {
        return self::$ffi->duckdb_to_timestamp($timestampStruct);
    }

    public function fromTimestamp(NativeCData $timestamp): NativeCData
    {
        return self::$ffi->duckdb_from_timestamp($timestamp);
    }

    public function createTimestamp(NativeCData $time): NativeCData
    {
        return self::$ffi->duckdb_create_timestamp($time);
    }

    public function createInterval(NativeCData $interval): NativeCData
    {
        return self::$ffi->duckdb_create_interval($interval);
    }

    public function createHugeint(NativeCData $hugeint): NativeCData
    {
        return self::$ffi->duckdb_create_hugeint($hugeint);
    }

    public function createUUID(NativeCData $hugeint): NativeCData
    {
        return self::$ffi->duckdb_create_uuid($hugeint);
    }

    public function createUhugeint(?NativeCData $uhugeint): NativeCData
    {
        return self::$ffi->duckdb_create_uhugeint($uhugeint);
    }

    public function getValueType(NativeCData $value): NativeCData
    {
        return self::$ffi->duckdb_get_value_type($value);
    }

    public function structVectorGetChild(NativeCData $struct, int $index): NativeCData
    {
        return self::$ffi->duckdb_struct_vector_get_child($struct, $index);
    }

    public function listVectorGetChild(NativeCData $list): NativeCData
    {
        return self::$ffi->duckdb_list_vector_get_child($list);
    }

    public function structTypeChildCount(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_struct_type_child_count($logicalType);
    }

    public function structTypeChildName(NativeCData $logicalType, int $index): NativeCData
    {
        return self::$ffi->duckdb_struct_type_child_name($logicalType, $index);
    }

    public function decimalInternalType(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_decimal_internal_type($logicalType);
    }

    public function enumDictionaryValue(NativeCData $logicalType, int $entry): string
    {
        return FFI::string(self::$ffi->duckdb_enum_dictionary_value($logicalType, $entry));
    }

    public function valueDecimal(NativeCData $result, int $col, int $row): NativeCData
    {
        return self::$ffi->duckdb_value_decimal($result, $col, $row);
    }

    public function getMapSize(NativeCData $map): int
    {
        return self::$ffi->duckdb_get_map_size($map);
    }

    public function getMapKey(NativeCData $map, int $index): NativeCData
    {
        return self::$ffi->duckdb_get_map_key($map, $index);
    }

    public function getMapValue(NativeCData $map, int $index): NativeCData
    {
        return self::$ffi->duckdb_get_map_value($map, $index);
    }

    public function arrayVectorGetChild(NativeCData $array): NativeCData
    {
        return self::$ffi->duckdb_array_vector_get_child($array);
    }

    public function arraySize(NativeCData $array): int
    {
        return self::$ffi->duckdb_array_type_array_size($array);
    }

    public function enumInternalType(NativeCData $logicalType): int
    {
        return self::$ffi->duckdb_enum_internal_type($logicalType);
    }

    public function columnCount(NativeCData $result): int
    {
        return self::$ffi->duckdb_column_count($result);
    }

    public static function string(NativeCData $string, ?int $length = null): string
    {
        return FFI::string($string, $length);
    }

    // Low performance function
    public function stringFromStringT(NativeCData $string): ?string
    {
        return self::$ffi->duckdb_string_t_data($string);
    }

    public function destroyLogicalType(NativeCData $logicalType): void
    {
        self::$ffi->duckdb_destroy_logical_type($logicalType);
    }

    public function getVarchar(NativeCData $duckdbValue): string
    {
        return self::$ffi->duckdb_get_varchar($duckdbValue);
    }

    public function createConfig(NativeCData $config): int
    {
        return self::$ffi->duckdb_create_config($config);
    }

    public function setConfig(?NativeCData $config, string $name, string $option): int
    {
        return self::$ffi->duckdb_set_config($config, $name, $option);
    }

    public function createAppender(
        NativeCData $connection,
        ?string $catalog,
        ?string $schema,
        string $table,
        ?NativeCData $appender,
    ): int {
        return self::$ffi->duckdb_appender_create_ext($connection, $catalog, $schema, $table, $appender);
    }

    public function appenderError(NativeCData $appender): string
    {
        return self::$ffi->duckdb_appender_error($appender);
    }

    public function appendValue(NativeCData $appender, NativeCData $value): int
    {
        return self::$ffi->duckdb_append_value($appender, $value);
    }

    public function endRow(NativeCData $appender): int
    {
        return self::$ffi->duckdb_appender_end_row($appender);
    }

    public function destroyAppender(NativeCData $appender): int
    {
        return self::$ffi->duckdb_appender_destroy($appender);
    }

    public function flush(NativeCData $appender): int
    {
        return self::$ffi->duckdb_appender_flush($appender);
    }

    public function createTimestampNs(NativeCData $nanos): NativeCData
    {
        return self::$ffi->duckdb_create_timestamp_ns($nanos);
    }

    public function destroyValue(NativeCData $value): void
    {
        self::$ffi->duckdb_destroy_value($value);
    }

    public function createInstanceCache(): NativeCData
    {
        return self::$ffi->duckdb_create_instance_cache();
    }

    public function getOrCreateFromCache(
        NativeCData $instanceCache,
        ?string $path,
        ?NativeCData $db,
        ?Configuration $config,
        ?string &$error,
    ): int {
        return self::$ffi->duckdb_get_or_create_from_cache($instanceCache, $path, $db, $config, $error);
    }

    public function destroyInstanceCache(NativeCData $instanceCache): void
    {
        self::$ffi->duckdb_destroy_instance_cache($instanceCache);
    }

    public function profilingInfo(?NativeCData $connection): ?NativeCData
    {
        return self::$ffi->duckdb_get_profiling_info($connection);
    }

    public function profilingInfoGetValue(NativeCData $profilingInfo, string $string): mixed
    {
        return self::$ffi->duckdb_profiling_info_get_value($profilingInfo, $string);
    }

    public function getDouble(NativeCData $duckdbValue): float
    {
        return self::$ffi->duckdb_get_double($duckdbValue);
    }

    public function createNull(): NativeCData
    {
        return self::$ffi->duckdb_create_null_value();
    }
}
