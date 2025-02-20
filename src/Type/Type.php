<?php

declare(strict_types=1);

namespace Saturio\DuckDB\Type;

enum Type: int
{
    case DUCKDB_TYPE_INVALID = 0;
    // bool
    case DUCKDB_TYPE_BOOLEAN = 1;
    // int8_t
    case DUCKDB_TYPE_TINYINT = 2;
    // int16_t
    case DUCKDB_TYPE_SMALLINT = 3;
    // int32_t
    case DUCKDB_TYPE_INTEGER = 4;
    // int64_t
    case DUCKDB_TYPE_BIGINT = 5;
    // uint8_t
    case DUCKDB_TYPE_UTINYINT = 6;
    // uint16_t
    case DUCKDB_TYPE_USMALLINT = 7;
    // uint32_t
    case DUCKDB_TYPE_UINTEGER = 8;
    // uint64_t
    case DUCKDB_TYPE_UBIGINT = 9;
    // float
    case DUCKDB_TYPE_FLOAT = 10;
    // double
    case DUCKDB_TYPE_DOUBLE = 11;
    // duckdb_timestamp (microseconds)
    case DUCKDB_TYPE_TIMESTAMP = 12;
    // duckdb_date
    case DUCKDB_TYPE_DATE = 13;
    // duckdb_time
    case DUCKDB_TYPE_TIME = 14;
    // duckdb_interval
    case DUCKDB_TYPE_INTERVAL = 15;
    // duckdb_hugeint
    case DUCKDB_TYPE_HUGEINT = 16;
    // duckdb_uhugeint
    case DUCKDB_TYPE_UHUGEINT = 32;
    // const char*
    case DUCKDB_TYPE_VARCHAR = 17;
    // duckdb_blob
    case DUCKDB_TYPE_BLOB = 18;
    // duckdb_decimal
    case DUCKDB_TYPE_DECIMAL = 19;
    // duckdb_timestamp_s (seconds)
    case DUCKDB_TYPE_TIMESTAMP_S = 20;
    // duckdb_timestamp_ms (milliseconds)
    case DUCKDB_TYPE_TIMESTAMP_MS = 21;
    // duckdb_timestamp_ns (nanoseconds)
    case DUCKDB_TYPE_TIMESTAMP_NS = 22;
    // enum type; only useful as logical type
    case DUCKDB_TYPE_ENUM = 23;
    // list type; only useful as logical type
    case DUCKDB_TYPE_LIST = 24;
    // struct type; only useful as logical type
    case DUCKDB_TYPE_STRUCT = 25;
    // map type; only useful as logical type
    case DUCKDB_TYPE_MAP = 26;
    // duckdb_array; only useful as logical type
    case DUCKDB_TYPE_ARRAY = 33;
    // duckdb_hugeint
    case DUCKDB_TYPE_UUID = 27;
    // union type; only useful as logical type
    case DUCKDB_TYPE_UNION = 28;
    // duckdb_bit
    case DUCKDB_TYPE_BIT = 29;
    // duckdb_time_tz
    case DUCKDB_TYPE_TIME_TZ = 30;
    // duckdb_timestamp (microseconds)
    case DUCKDB_TYPE_TIMESTAMP_TZ = 31;
    // ANY type
    case DUCKDB_TYPE_ANY = 34;
    // duckdb_varint
    case DUCKDB_TYPE_VARINT = 35;
    // SQLNULL type
    case DUCKDB_TYPE_SQLNULL = 36;
}
