<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Exception\QueryException;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\UUID;

class QueryTest extends TestCase
{
    private DuckDB $db;

    protected function setUp(): void
    {
        $this->db = DuckDB::create();
    }

    public function testSyntaxError(): void
    {
        $this->expectException(QueryException::class);
        $this->db->query('This is not a valid query');
    }

    #[Group('primitives')]
    public function testBoolSelect(): void
    {
        $expectedValues = [[true, false], [false, null], [false, false]];
        $result = $this->db->query('SELECT * FROM (VALUES (true,false),(false ,null),(false,false));');

        foreach ($result->rows() as $row) {
            $this->assertEquals(array_shift($expectedValues), $row);
        }
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testSimpleIntegerSelect(): void
    {
        $expectedValue = [6];
        $result = $this->db->query('SELECT 6 as my_column;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testMultipleIntegerSelect(): void
    {
        $expectedValues = [[6, 5], [2, 5], [3, 7]];
        $result = $this->db->query('SELECT * FROM (VALUES (6,5),(2,5),(3,7));');

        foreach ($result->rows() as $row) {
            $this->assertEquals(array_shift($expectedValues), $row);
        }
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testSimpleIntegerSizes(): void
    {
        $expectedValue = [1, 2, 3, 4, 5, 6, 7, 8];
        $result = $this->db->query('SELECT 
            1::TINYINT,
            2::SMALLINT,
            3::INTEGER,
            4::BIGINT,
            5::UTINYINT,
            6::USMALLINT,
            7::UINTEGER,
            8::UBIGINT
        as my_column;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testIntegerGreaterThanPhpMax(): void
    {
        $maxPhp = PHP_INT_MAX;
        $maxUBigIntDuckDB = '18446744073709551615';
        $expectedValue = [[bcadd((string) PHP_INT_MAX, '1')], [$maxUBigIntDuckDB]];
        $this->db->query('CREATE TABLE ubigints(int UBIGINT NULL)');
        $this->db->query("INSERT INTO ubigints VALUES ({$maxPhp})");
        $this->db->query('UPDATE ubigints SET int = int + 1;');
        $this->db->query("INSERT INTO ubigints VALUES ({$maxUBigIntDuckDB})");
        $result = $this->db->query('SELECT * FROM ubigints;');

        foreach ($result->rows() as $row) {
            $this->assertEquals(array_shift($expectedValue), $row);
        }
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testUHugeInt(): void
    {
        $expectedValue = ['340282366920938463463374607431768211455'];
        $result = $this->db->query('SELECT 340282366920938463463374607431768211455::UHUGEINT as highest_allowed_number;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('integers')]
    #[Group('numerics')]
    public function testUHugeInt2(): void
    {
        $expectedValue = ['3446217245712155457173499895997437'];
        $result = $this->db->query('SELECT 3446217245712155457173499895997437::UHUGEINT as highest_allowed_number;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testUHugeIntMid(): void
    {
        $expectedValue = ['200282366920938463463374607431768211455'];
        $result = $this->db->query('SELECT 200282366920938463463374607431768211455::UHUGEINT as highest_allowed_number;');
        $row = $result->rows()->current();

        $result = $this->db->query('SELECT 170141183460469231731687303715884105727::UHUGEINT as highest_allowed_number;');
        $row = $result->rows()->current();
        $result = $this->db->query('SELECT 170141183460469231731687303715884105728::UHUGEINT as highest_allowed_number;');
        $row = $result->rows()->current();
        $result = $this->db->query('SELECT 340282366920938463463374607431768211455::UHUGEINT as highest_allowed_number;');
        $row = $result->rows()->current();
        $result = $this->db->query('SELECT 200282366920938463463374607431768211455::UHUGEINT as highest_allowed_number;');
        $row = $result->rows()->current();

        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testHugeInt(): void
    {
        $expectedValue = ['170141183460469231731687303715884105727'];
        $result = $this->db->query('SELECT 170141183460469231731687303715884105727::HUGEINT as highest_allowed_number;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testHugeIntNegative(): void
    {
        $expectedValue = ['-170141183460469231731687303715884105727'];
        $result = $this->db->query('SELECT -170141183460469231731687303715884105727::HUGEINT as highest_allowed_number;');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('integers')]
    #[Group('numerics')]
    public function testIntegerSelect(): void
    {
        $expectedValues = [0 => [12], 1 => [null]];
        $this->db->query('DROP TABLE IF EXISTS integers');
        $this->db->query('CREATE TABLE integers(int INTEGER NULL)');
        $this->db->query('INSERT INTO integers VALUES (NULL), (12)');

        $result = $this->db->query('SELECT * FROM integers ORDER BY int');
        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testDecimalSelect(): void
    {
        $expectedValues = [0 => [12.3], 1 => [null]];
        $this->db->query('DROP TABLE IF EXISTS decimals');
        $this->db->query('CREATE TABLE decimals(dec DECIMAL NULL)');
        $this->db->query('INSERT INTO decimals VALUES (NULL), (12.3)');

        $result = $this->db->query('SELECT * FROM decimals ORDER BY dec');
        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testDecimalSelectWithoutType(): void
    {
        $expectedValues = [1.12, 275.12];
        $result = $this->db->query('SELECT * FROM (VALUES (1.12, 275.12));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testDecimalStoredInternallyAsHugeInt(): void
    {
        $expectedValues = [1000000000000000000.12, 275000000.12];
        $result = $this->db->query('SELECT * FROM (VALUES (1000000000000000000.12, 275000000.12));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testValueCastedToDecimalWithoutPositions(): void
    {
        $expectedValues = [1.12, 275.12];
        $result = $this->db->query('SELECT * FROM (VALUES (1.12::DECIMAL,275.12::DECIMAL));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testValueCastedToDecimal(): void
    {
        $expectedValues = [125.12, 275.12];
        $result = $this->db->query('SELECT * FROM (VALUES (125.12345::DECIMAL(10,2),275.12345::DECIMAL(10,2)));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('floats')]
    #[Group('numerics')]
    public function testValueCastedToFourDecimalPositions(): void
    {
        $expectedValues = [125.1235, 275.1235];
        $result = $this->db->query('SELECT * FROM (VALUES (125.12345::DECIMAL(10,4),275.12345::DECIMAL(10,4)));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testFloatSelect(): void
    {
        $expectedValues = [125.1, 275.1];
        $result = $this->db->query('SELECT * FROM (VALUES (125.1::FLOAT, 275.1::FLOAT));');

        $row = $result->rows()->current();
        $this->assertEqualsWithDelta($expectedValues, $row, delta: 0.00001);
    }

    #[Group('primitives')]
    public function testDoubleSelect(): void
    {
        $expectedValues = [125.1, 275.1];
        $result = $this->db->query('SELECT * FROM (VALUES (125.1::DOUBLE, 275.1::DOUBLE));');

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampSelect(): void
    {
        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(11, 30, 0, microseconds: 123456)
        )];
        $result = $this->db->query("SELECT TIMESTAMP '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampInfinityAndEpochSelect(): void
    {
        $expectedValues = [
            new Timestamp(infinity: -1),
            new Timestamp(new Date(1970, 1, 1), new Time(0, 0, 0)),
            new Timestamp(infinity: 1),
        ];
        $result = $this->db->query("SELECT '-infinity'::TIMESTAMP, 'epoch'::TIMESTAMP, 'infinity'::TIMESTAMP;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampMsSelect(): void
    {
        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(11, 30, 0, milliseconds: 123)
        )];
        $result = $this->db->query("SELECT TIMESTAMP_MS '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampSSelect(): void
    {
        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(11, 30, 0)
        )];
        $result = $this->db->query("SELECT TIMESTAMP_S '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampNsSelect(): void
    {
        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(11, 30, 0, nanoseconds: 123456789)
        )];
        $result = $this->db->query("SELECT TIMESTAMP_NS '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('timestamp')]
    public function testTimestampTzSelect(): void
    {
        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(11, 30, 0, microseconds: 123456, isTimeZoned: true)
        )];
        $this->db->query("SET TimeZone = 'UTC';");
        $result = $this->db->query("SELECT TIMESTAMPTZ '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);

        $expectedValues = [new Timestamp(
            new Date(1992, 9, 20),
            new Time(12, 30, 0, microseconds: 123456, isTimeZoned: true)
        )];
        $this->db->query("SET TimeZone = 'Etc/GMT+1';");
        $result = $this->db->query("SELECT TIMESTAMPTZ '1992-09-20 11:30:00.123456789';");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testDateSelect(): void
    {
        $expectedValues = [new Date(1992, 9, 20)];

        $result = $this->db->query("SELECT '1992-09-20'::DATE;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testTimeSelect(): void
    {
        $expectedValues = [new Time(11, 30, 0, microseconds: 123456)];

        $result = $this->db->query("SELECT '11:30:00.123456'::TIME;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testTimeTzSelect(): void
    {
        $expectedValues = [new Time(11, 30, 0, microseconds: 123456, isTimeZoned: true, offset: 0)];

        $this->db->query("SET TimeZone = 'UTC';");
        $result = $this->db->query("SELECT '11:30:00.123456'::TIMETZ;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);

        $expectedValues = [new Time(11, 30, 0, microseconds: 123456, isTimeZoned: true, offset: -3600)];

        $this->db->query("SET TimeZone = 'Etc/GMT+1';");
        $result = $this->db->query("SELECT '11:30:00.123456'::TIMETZ;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testBitSelect(): void
    {
        $expectedValues = '10101010111010101';

        $result = $this->db->query("SELECT '10101010111010101'::BITSTRING;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row[0]);
    }

    #[Group('primitives')]
    public function testVarIntSelect(): void
    {
        $expectedValues = ['12312', '-123456789123456789', '123456789123456789', '123456789123456789123456789123456789123456789123456789'];

        $result = $this->db->query("SELECT '12312'::VARINT, '-123456789123456789'::VARINT, '123456789123456789'::VARINT, '123456789123456789123456789123456789123456789123456789'::VARINT;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testBlobSelect(): void
    {
        $expectedValues = ['123\xAA\xAB\xAC'];

        $result = $this->db->query("SELECT '123\\xAA\\xAB\\xAC'::BLOB;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    public function testReadBlob(): void
    {
        $file = __DIR__.'/../../lib/linux-arm64/libduckdb.so';
        $contents = file_get_contents($file);
        $expectedValues = [
            0 => strlen($contents),
            1 => $contents,
        ];
        $result = $this->db->query("SELECT size, content FROM read_blob('{$file}');");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues[0], $row[0]);
        $this->assertEquals($expectedValues[1], $row[1]->data());
    }

    #[Group('primitives')]
    public function testIntervalSelect(): void
    {
        $expectedValues = [
            new Interval(months: 12),
            new Interval(months: 1, days: 1),
            new Interval(months: 16),
            new Interval(microseconds: intval(48 * 60 * 60 * 1e6)),
        ];

        $result = $this->db->query("SELECT
            INTERVAL 1 YEAR, -- single unit using YEAR keyword; stored as 12 months
            INTERVAL '1 month 1 day', -- string type necessary for multiple units; stored as (1 month, 1 day)
            '16 months'::INTERVAL, -- string cast supported; stored as 16 months
            '48:00:00'::INTERVAL, -- HH::MM::SS string supported; stored as (48 * 60 * 60 * 1e6 microseconds)
        ;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    public function testUUIDSelect(): void
    {
        $expectedValues = [new UUID('8000a9e9-607c-4c8a-84f3-843f0191e3fd')];
        $result = $this->db->query("SELECT '8000a9e9-607c-4c8a-84f3-843f0191e3fd'::UUID;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);

        $expectedValues = [new UUID('0000a9e9-607c-4c8a-84f3-843f0191e3fd')];
        $result = $this->db->query("SELECT '0000a9e9-607c-4c8a-84f3-843f0191e3fd'::UUID;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);

        $expectedValues = [new UUID('d259392c-3f7a-46bf-9d09-692ae7582058')];
        $result = $this->db->query("SELECT 'd259392c-3f7a-46bf-9d09-692ae7582058'::UUID;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    #[Group('primitives')]
    #[Group('string')]
    public function testSimpleStringSelect(): void
    {
        $expectedValue = ['quack'];
        $result = $this->db->query("SELECT 'quack' as my_column;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('string')]
    public function testLongString(): void
    {
        $expectedValue = ['quack quack quack quack quack quack quack quack quack quack'];
        $result = $this->db->query("SELECT 'quack quack quack quack quack quack quack quack quack quack' as my_column;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('string')]
    public function testVarcharEmojis(): void
    {
        $expectedValue = ['🦆🦆🦆🦆'];
        $result = $this->db->query("SELECT '🦆🦆🦆🦆'::VARCHAR as my_column;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('nested')]
    public function testVarcharArray(): void
    {
        $expectedValue = [['🦆🦆🦆🦆🦆🦆', 'goose', null, '']];
        $result = $this->db->query("SELECT ['🦆🦆🦆🦆🦆🦆', 'goose', null, '']::VARCHAR[] as my_column;");

        $row = $result->rows()->current();
        $this->assertEquals($expectedValue, $row);
    }

    #[Group('primitives')]
    #[Group('string')]
    public function testMultipleStringSelect(): void
    {
        $expectedValues = [
            ['this-is-a-long-query-and-says-quack', 'quack'],
            ['quick', 'queck'],
            ['q', ''],
        ];
        $result = $this->db->query(
            "SELECT * FROM (VALUES ('this-is-a-long-query-and-says-quack','quack'),('quick','queck'),('q',''));"
        );

        foreach ($result->rows() as $row) {
            $this->assertEquals(array_shift($expectedValues), $row);
        }
    }

    #[Group('primitives')]
    #[Group('string')]
    public function testVarChars(): void
    {
        $expectedValues = [['quack', 'queck'], ['quick', null], ['duck', 'cool']];
        $result = $this->db->query("SELECT * FROM (VALUES ('quack', 'queck'), ('quick', NULL), ('duck', 'cool'));");

        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('nested')]
    public function testStruct(): void
    {
        $expectedValue = [['birds' => 2, 'aliens' => null, 'amphibians' => 3], ['birds' => 1]];
        $result = $this->db->query("SELECT {'birds': 2, 'aliens': NULL, 'amphibians': 3}, {'birds': 1};");

        foreach ($result->rows() as $row) {
            $this->assertEquals($expectedValue, $row);
        }
    }

    #[Group('nested')]
    public function testListsSelect(): void
    {
        $expectedValue = [['duck', 'goose', 'heron']];
        $result = $this->db->query("SELECT ['duck', 'goose', 'heron'];");

        foreach ($result->rows() as $row) {
            $this->assertEquals($expectedValue, $row);
        }
    }

    #[Group('nested')]
    public function testStructWithLists(): void
    {
        $expectedValue = [['birds' => ['duck', 'goose', 'heron'], 'aliens' => null, 'amphibians' => ['frog', 'toad']]];
        $result = $this->db->query("SELECT {'birds': ['duck', 'goose', 'heron'], 'aliens': NULL, 'amphibians': ['frog', 'toad']};");

        foreach ($result->rows() as $row) {
            $this->assertEquals($expectedValue, $row);
        }
    }

    #[Group('nested')]
    public function testStructWithListOfMaps(): void
    {
        $expectedValue = [['test' => [[1 => 42.1, 5 => 45], [1 => 42.1, 5 => 45]]]];
        $result = $this->db->query("SELECT {'test': [MAP([1, 5], [42.1, 45]), MAP([1, 5], [42.1, 45])]};");

        foreach ($result->rows() as $row) {
            $this->assertEquals($expectedValue, $row);
        }
    }

    #[Group('nested')]
    public function testMultirowList(): void
    {
        $expectedValues = [0 => [['duck', 'goose', 'heron']], 1 => [['duck2', 'goose2', 'heron2']]];
        $this->db->query('DROP TABLE IF EXISTS lists');
        $this->db->query('CREATE TABLE lists(list VARCHAR[] NULL)');
        $this->db->query("INSERT INTO lists VALUES (['duck', 'goose', 'heron']), (['duck2', 'goose2', 'heron2'])");

        $result = $this->db->query('SELECT * FROM lists');
        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('nested')]
    public function testMultirowArray(): void
    {
        $expectedValues = [0 => [['duck', 'goose', 'heron']], 1 => [['duck2', 'goose2', 'heron2']]];
        $this->db->query('DROP TABLE IF EXISTS lists');
        $this->db->query('CREATE TABLE lists(list VARCHAR[3] NULL)');
        $this->db->query("INSERT INTO lists VALUES (['duck', 'goose', 'heron']), (['duck2', 'goose2', 'heron2'])");

        $result = $this->db->query('SELECT * FROM lists');
        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('nested')]
    public function testUnions(): void
    {
        $expectedValues = [[1], ['two'], ['three'], [4], [null]];
        $this->db->query('DROP TABLE IF EXISTS unions');
        $this->db->query('CREATE TABLE unions (u UNION(num INTEGER, str VARCHAR) NULL);');
        $this->db->query("INSERT INTO unions values (1), ('two'), (union_value(str := 'three')), (4), (null);");

        $result = $this->db->query('SELECT u FROM unions');
        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('nested')]
    public function testEnum(): void
    {
        $expectedValues = [['happy']];
        $result = $this->db->query("SELECT 'happy'::ENUM ('sad', 'ok', 'happy');");

        foreach ($result->rows() as $id => $row) {
            $this->assertEquals($expectedValues[$id], $row);
        }
    }

    #[Group('nested')]
    public function testArray(): void
    {
        $expectedValues = [[1, 2, 3]];
        $result = $this->db->query('SELECT array_value(1, 2, 3);');

        $this->assertEquals($expectedValues, $result->rows()->current());
    }

    public function testColumnNames(): void
    {
        $expectedValues = ['column1', 'column2', 'column3'];
        $result = $this->db->query("SELECT 'quack' as column1, 'queck' as column2, 'quick' as column3;");

        $this->assertEquals($expectedValues, iterator_to_array($result->columnNames()));
    }
}
