## Types

From version 1.2.0 on the library supports all DuckDB file types.

| DuckDB Type              | SQL Type     | PHP Type                             |
|--------------------------|--------------|--------------------------------------|
| DUCKDB_TYPE_BOOLEAN      | BOOLEAN      | bool                                 |
| DUCKDB_TYPE_TINYINT      | TINYINT      | int                                  |
| DUCKDB_TYPE_SMALLINT     | SMALLINT     | int                                  |
| DUCKDB_TYPE_INTEGER      | INTEGER      | int                                  |
| DUCKDB_TYPE_BIGINT       | BIGINT       | int                                  |
| DUCKDB_TYPE_UTINYINT     | UTINYINT     | int                                  |
| DUCKDB_TYPE_USMALLINT    | USMALLINT    | int                                  |
| DUCKDB_TYPE_UINTEGER     | UINTEGER     | int                                  |
| DUCKDB_TYPE_UBIGINT      | UBIGINT      | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_FLOAT        | FLOAT        | float                                |
| DUCKDB_TYPE_DOUBLE       | DOUBLE       | float                                |
| DUCKDB_TYPE_TIMESTAMP    | TIMESTAMP    | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_DATE         | DATE         | Saturio\DuckDB\Type\Date             |
| DUCKDB_TYPE_TIME         | TIME         | Saturio\DuckDB\Type\Time             |
| DUCKDB_TYPE_INTERVAL     | INTERVAL     | Saturio\DuckDB\Type\Interval         |
| DUCKDB_TYPE_HUGEINT      | HUGEINT      | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_UHUGEINT     | UHUGEINT     | Saturio\DuckDB\Type\Math\LongInteger |
| DUCKDB_TYPE_VARCHAR      | VARCHAR      | string                               |
| DUCKDB_TYPE_BLOB         | BLOB         | Saturio\DuckDB\Type\Blob             |
| DUCKDB_TYPE_TIMESTAMP_S  | TIMESTAMP_S  | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_TIMESTAMP_MS | TIMESTAMP_MS | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_TIMESTAMP_NS | TIMESTAMP_NS | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_UUID         | UUID         | Saturio\DuckDB\Type\UUID             |
| DUCKDB_TYPE_TIME_TZ      | TIMETZ       | Saturio\DuckDB\Type\Time             |
| DUCKDB_TYPE_TIMESTAMP_TZ | TIMESTAMPTZ  | Saturio\DuckDB\Type\Timestamp        |
| DUCKDB_TYPE_DECIMAL      | DECIMAL      | float                                |
| DUCKDB_TYPE_ENUM         | ENUM         | string                               |
| DUCKDB_TYPE_LIST         | LIST         | array                                |
| DUCKDB_TYPE_STRUCT       | STRUCT       | array                                |
| DUCKDB_TYPE_ARRAY        | ARRAY        | array                                |
| DUCKDB_TYPE_MAP          | MAP          | array                                |
| DUCKDB_TYPE_UNION        | UNION        | mixed                                |
| DUCKDB_TYPE_BIT          | BIT          | string                               |
| DUCKDB_TYPE_VARINT       | VARINT       | string                               |
| DUCKDB_TYPE_SQLNULL      | NULL         | null                                 |
