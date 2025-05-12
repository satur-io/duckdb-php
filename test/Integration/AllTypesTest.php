<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;

class AllTypesTest extends TestCase
{
    private DuckDB $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = DuckDB::create();
    }

    public function testAllTypes()
    {
        $result = $this->db->query('SELECT * FROM test_all_types();');

        self::assertEquals(54, $result->columnCount());
        $jsonResult = json_encode(
            iterator_to_array($result->rows(columnNameAsKey: true)),
            flags: JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        );
        $this->assertJsonStringEqualsJsonString($this->getExpectedResult(), $jsonResult);
    }

    private function getExpectedResult(): string
    {
        return <<<JSON
[
    {
        "bool": false,
        "tinyint": -128,
        "smallint": -32768,
        "int": -2147483648,
        "bigint": -9223372036854775808,
        "hugeint": "-170141183460469231731687303715884105728",
        "uhugeint": "0",
        "utinyint": 0,
        "usmallint": 0,
        "uint": 0,
        "ubigint": 0,
        "varint": "-179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368",
        "date": "-5877641-06-25",
        "time": "00:00:00.0",
        "timestamp": "-290308-12-22 00:00:00.0",
        "timestamp_s": "-290308-12-22 00:00:00.0",
        "timestamp_ms": "-290308-12-22 00:00:00.0",
        "timestamp_ns": "1677-09-22 00:00:00.0",
        "time_tz": "00:00:00.0",
        "timestamp_tz": "-290308-12-22 00:00:00.0",
        "float": -3.4028234663852886e+38,
        "double": -1.7976931348623157e+308,
        "dec_4_1": -999.9,
        "dec_9_4": -99999.9999,
        "dec_18_6": -1000000000000,
        "dec38_10": -1.0e+28,
        "uuid": "00000000-0000-0000-0000-000000000000",
        "interval": "0 months 0 days 0 microseconds",
        "varchar": "🦆🦆🦆🦆🦆🦆",
        "blob": "thisisalongblob\\\\x00withnullbytes",
        "bit": "0010001001011100010101011010111",
        "small_enum": "DUCK_DUCK_ENUM",
        "medium_enum": "enum_0",
        "large_enum": "enum_0",
        "int_array": [],
        "double_array": [],
        "date_array": [],
        "timestamp_array": [],
        "timestamptz_array": [],
        "varchar_array": [],
        "nested_int_array": [],
        "struct": {
            "a": null,
            "b": null
        },
        "struct_of_arrays": {
            "a": null,
            "b": null
        },
        "array_of_structs": [],
        "map": [],
        "union": "Frank",
        "fixed_int_array": [
            null,
            2,
            3
        ],
        "fixed_varchar_array": [
            "a",
            null,
            "c"
        ],
        "fixed_nested_int_array": [
            [
                null,
                2,
                3
            ],
            null,
            [
                null,
                2,
                3
            ]
        ],
        "fixed_nested_varchar_array": [
            [
                "a",
                null,
                "c"
            ],
            null,
            [
                "a",
                null,
                "c"
            ]
        ],
        "fixed_struct_array": [
            {
                "a": null,
                "b": null
            },
            {
                "a": 42,
                "b": "🦆🦆🦆🦆🦆🦆"
            },
            {
                "a": null,
                "b": null
            }
        ],
        "struct_of_fixed_array": {
            "a": [
                null,
                2,
                3
            ],
            "b": [
                "a",
                null,
                "c"
            ]
        },
        "fixed_array_of_int_list": [
            [],
            [
                42,
                999,
                null,
                null,
                -42
            ],
            []
        ],
        "list_of_fixed_int_array": [
            [
                null,
                2,
                3
            ],
            [
                4,
                5,
                6
            ],
            [
                null,
                2,
                3
            ]
        ]
    },
    {
        "bool": true,
        "tinyint": 127,
        "smallint": 32767,
        "int": 2147483647,
        "bigint": 9223372036854775807,
        "hugeint": "170141183460469231731687303715884105727",
        "uhugeint": "340282366920938463463374607431768211455",
        "utinyint": 255,
        "usmallint": 65535,
        "uint": 4294967295,
        "ubigint": "18446744073709551615",
        "varint": "179769313486231570814527423731704356798070567525844996598917476803157260780028538760589558632766878171540458953514382464234321326889464182768467546703537516986049910576551282076245490090389328944075868508455133942304583236903222948165808559332123348274797826204144723168738177180919299881250404026184124858368",
        "date": "5881580-07-10",
        "time": "24:00:00.0",
        "timestamp": "294247-01-10 04:00:54.775806000",
        "timestamp_s": "294247-01-10 04:00:54.0",
        "timestamp_ms": "294247-01-10 04:00:54.775000000",
        "timestamp_ns": "2262-04-11 23:47:16.854775806",
        "time_tz": "24:00:00.0",
        "timestamp_tz": "294247-01-10 04:00:54.775806000",
        "float": 3.4028234663852886e+38,
        "double": 1.7976931348623157e+308,
        "dec_4_1": 999.9,
        "dec_9_4": 99999.9999,
        "dec_18_6": 1000000000000,
        "dec38_10": 1.0e+28,
        "uuid": "ffffffff-ffff-ffff-ffff-ffffffffffff",
        "interval": "999 months 999 days 999999999 microseconds",
        "varchar": "goo\u0000se",
        "blob": "\\\\x00\\\\x00\\\\x00a",
        "bit": "10101",
        "small_enum": "GOOSE",
        "medium_enum": "enum_299",
        "large_enum": "enum_69999",
        "int_array": [
            42,
            999,
            null,
            null,
            -42
        ],
        "double_array": [
            42,
            0,
            0,
            0,
            null,
            -42
        ],
        "date_array": [
            "1970-01-01",
            "5881580-07-11",
            "-5877641-06-24",
            null,
            "2022-05-12"
        ],
        "timestamp_array": [
            "1970-01-01 00:00:00.0",
            "+infinity",
            "-infinity",
            null,
            "2022-05-12 16:23:45.0"
        ],
        "timestamptz_array": [
            "1970-01-01 00:00:00.0",
            "+infinity",
            "-infinity",
            null,
            "2022-05-12 23:23:45.0"
        ],
        "varchar_array": [
            "🦆🦆🦆🦆🦆🦆",
            "goose",
            null,
            ""
        ],
        "nested_int_array": [
            [],
            [
                42,
                999,
                null,
                null,
                -42
            ],
            null,
            [],
            [
                42,
                999,
                null,
                null,
                -42
            ]
        ],
        "struct": {
            "a": 42,
            "b": "🦆🦆🦆🦆🦆🦆"
        },
        "struct_of_arrays": {
            "a": [
                42,
                999,
                null,
                null,
                -42
            ],
            "b": [
                "🦆🦆🦆🦆🦆🦆",
                "goose",
                null,
                ""
            ]
        },
        "array_of_structs": [
            {
                "a": null,
                "b": null
            },
            {
                "a": 42,
                "b": "🦆🦆🦆🦆🦆🦆"
            },
            null
        ],
        "map": {
            "key1": "🦆🦆🦆🦆🦆🦆",
            "key2": "goose"
        },
        "union": 5,
        "fixed_int_array": [
            4,
            5,
            6
        ],
        "fixed_varchar_array": [
            "d",
            "e",
            "f"
        ],
        "fixed_nested_int_array": [
            [
                4,
                5,
                6
            ],
            [
                null,
                2,
                3
            ],
            [
                4,
                5,
                6
            ]
        ],
        "fixed_nested_varchar_array": [
            [
                "d",
                "e",
                "f"
            ],
            [
                "a",
                null,
                "c"
            ],
            [
                "d",
                "e",
                "f"
            ]
        ],
        "fixed_struct_array": [
            {
                "a": 42,
                "b": "🦆🦆🦆🦆🦆🦆"
            },
            {
                "a": null,
                "b": null
            },
            {
                "a": 42,
                "b": "🦆🦆🦆🦆🦆🦆"
            }
        ],
        "struct_of_fixed_array": {
            "a": [
                4,
                5,
                6
            ],
            "b": [
                "d",
                "e",
                "f"
            ]
        },
        "fixed_array_of_int_list": [
            [
                42,
                999,
                null,
                null,
                -42
            ],
            [],
            [
                42,
                999,
                null,
                null,
                -42
            ]
        ],
        "list_of_fixed_int_array": [
            [
                4,
                5,
                6
            ],
            [
                null,
                2,
                3
            ],
            [
                4,
                5,
                6
            ]
        ]
    },
    {
        "bool": null,
        "tinyint": null,
        "smallint": null,
        "int": null,
        "bigint": null,
        "hugeint": null,
        "uhugeint": null,
        "utinyint": null,
        "usmallint": null,
        "uint": null,
        "ubigint": null,
        "varint": null,
        "date": null,
        "time": null,
        "timestamp": null,
        "timestamp_s": null,
        "timestamp_ms": null,
        "timestamp_ns": null,
        "time_tz": null,
        "timestamp_tz": null,
        "float": null,
        "double": null,
        "dec_4_1": null,
        "dec_9_4": null,
        "dec_18_6": null,
        "dec38_10": null,
        "uuid": null,
        "interval": null,
        "varchar": null,
        "blob": null,
        "bit": null,
        "small_enum": null,
        "medium_enum": null,
        "large_enum": null,
        "int_array": [],
        "double_array": [],
        "date_array": [],
        "timestamp_array": [],
        "timestamptz_array": [],
        "varchar_array": [],
        "nested_int_array": [],
        "struct": {
            "a": null,
            "b": null
        },
        "struct_of_arrays": {
            "a": null,
            "b": null
        },
        "array_of_structs": [],
        "map": [],
        "union": null,
        "fixed_int_array": [
            null,
            null,
            null
        ],
        "fixed_varchar_array": [
            null,
            null,
            null
        ],
        "fixed_nested_int_array": [
            null,
            null,
            null
        ],
        "fixed_nested_varchar_array": [
            null,
            null,
            null
        ],
        "fixed_struct_array": [
            null,
            null,
            null
        ],
        "struct_of_fixed_array": {
            "a": null,
            "b": null
        },
        "fixed_array_of_int_list": [
            null,
            null,
            null
        ],
        "list_of_fixed_int_array": []
    }
]
JSON;
    }
}
