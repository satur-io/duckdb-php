<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

enum TypeC: string
{
    case DUCKDB_TYPE_INVALID = '';
    // bool
    case DUCKDB_TYPE_BOOLEAN = 'bool';
    // int8_t
    case DUCKDB_TYPE_TINYINT = 'int8_t';
    // int16_t
    case DUCKDB_TYPE_SMALLINT = 'int16_t';
    // int32_t
    case DUCKDB_TYPE_INTEGER = 'int32_t';
    // int64_t
    case DUCKDB_TYPE_BIGINT = 'int64_t';
    // uint8_t
    case DUCKDB_TYPE_UTINYINT = 'uint8_t';
    // uint16_t
    case DUCKDB_TYPE_USMALLINT = 'uint16_t';
    // uint32_t
    case DUCKDB_TYPE_UINTEGER = 'uint32_t';
    // uint64_t
    case DUCKDB_TYPE_UBIGINT = 'uint64_t';
    // float
    case DUCKDB_TYPE_FLOAT = 'float';
    // double
    case DUCKDB_TYPE_DOUBLE = 'double';
    // duckdb_timestamp (microseconds)
    case DUCKDB_TYPE_TIMESTAMP = 'duckdb_timestamp';
    // duckdb_date
    case DUCKDB_TYPE_DATE = 'duckdb_date';
    // duckdb_time
    case DUCKDB_TYPE_TIME = 'duckdb_time';
    // duckdb_interval
    case DUCKDB_TYPE_INTERVAL = 'duckdb_interval';
    // duckdb_hugeint
    case DUCKDB_TYPE_HUGEINT = 'duckdb_hugeint';
    // duckdb_uhugeint
    case DUCKDB_TYPE_UHUGEINT = 'duckdb_uhugeint';
    // const char*
    case DUCKDB_TYPE_VARCHAR = 'duckdb_string_t';
    // duckdb_blob
    case DUCKDB_TYPE_BLOB = 'duckdb_blob';
    // duckdb_decimal
    case DUCKDB_TYPE_DECIMAL = 'duckdb_decimal';
    // duckdb_timestamp_s (seconds)
    case DUCKDB_TYPE_TIMESTAMP_S = 'duckdb_timestamp_s';
    // duckdb_timestamp_ms (milliseconds)
    case DUCKDB_TYPE_TIMESTAMP_MS = 'duckdb_timestamp_ms';
    // duckdb_timestamp_ns (nanoseconds)
    case DUCKDB_TYPE_TIMESTAMP_NS = 'duckdb_timestamp_ns';
    // enum type; only useful as logical type
    case DUCKDB_TYPE_ENUM = 'enum';
    // list type; only useful as logical type
    case DUCKDB_TYPE_LIST = 'list';
    // struct type; only useful as logical type
    case DUCKDB_TYPE_STRUCT = 'struct';
    // map type; only useful as logical type
    case DUCKDB_TYPE_MAP = 'map';
    // duckdb_array; only useful as logical type
    case DUCKDB_TYPE_ARRAY = 'duckdb_array';
    // duckdb_hugeint
    case DUCKDB_TYPE_UUID = 'duckdb_uuid';
    // union type; only useful as logical type
    case DUCKDB_TYPE_UNION = 'union';
    // duckdb_bit
    case DUCKDB_TYPE_BIT = 'duckdb_bit';
    // duckdb_time_tz
    case DUCKDB_TYPE_TIME_TZ = 'duckdb_time_tz';
    // duckdb_timestamp (microseconds)
    case DUCKDB_TYPE_TIMESTAMP_TZ = 'duckdb_timestamp_tz';
    // ANY type
    case DUCKDB_TYPE_ANY = 'any';
    // duckdb_varint
    case DUCKDB_TYPE_VARINT = 'duckdb_varint';
    // SQLNULL type
    case DUCKDB_TYPE_SQLNULL = 'sqlnull';
}
