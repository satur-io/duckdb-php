<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Type\Blob;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\Type;

class PreparedStatementTest extends TestCase
{
    private DuckDB $db;

    protected function setUp(): void
    {
        $this->db = DuckDB::create();

        $this->db->query('CREATE TABLE test_data (i INTEGER, b BOOL, f FLOAT);');
        $this->db->query('INSERT INTO test_data VALUES (3, true, 1.1), (5, true, 1.2), (3, false, 1.1), (3, null, 1.2);');
    }

    public function testPreparedStatementBool(): void
    {
        $expectedResult = [[3, true, 1.1], [5, true, 1.2]];
        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_data WHERE b = $1');
        $preparedStatement->bindParam(1, true);
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEqualsWithDelta($expectedResult, $arrayResult, delta: 0.0000001);
    }

    public function testPreparedStatementInt(): void
    {
        $expectedResult = [[3, true, 1.1], [3, false, 1.1], [3, null, 1.2]];
        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_data WHERE i = ?');
        $preparedStatement->bindParam(1, 3);
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEqualsWithDelta($expectedResult, $arrayResult, delta: 0.0000001);
    }

    public function testPreparedStatementFloat(): void
    {
        $expectedResult = [[3, true, 1.1], [3, false, 1.1]];
        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_data WHERE f = ?');
        $preparedStatement->bindParam(1, 1.1);
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEqualsWithDelta($expectedResult, $arrayResult, delta: 0.0000001);
    }

    public function testPreparedStatementString(): void
    {
        $this->db->query('CREATE TABLE test_string (i INTEGER, s VARCHAR);');
        $this->db->query("INSERT INTO test_string VALUES (3, 'Hello'), (5, 'Duck'), (3, ''), (3, null);");

        $expectedResult = [[5, 'Duck']];

        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_string WHERE s = ?');
        $preparedStatement->bindParam(1, 'Duck');
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
    }

    public function testPreparedStatementDate(): void
    {
        $this->db->query('CREATE TABLE test_date (i INTEGER, d DATE);');
        $this->db->query("INSERT INTO test_date VALUES (3, '2024-07-07'), (5, '1521-04-23'), (3, null);");

        $expectedResult = [[5, new Date(1521, 04, 23)]];

        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_date WHERE d = ?');
        $preparedStatement->bindParam(1, new Date(1521, 04, 23));
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
    }

    public function testPreparedStatementTime(): void
    {
        $this->db->query('CREATE TABLE test_time (i INTEGER, t TIME);');
        $this->db->query("INSERT INTO test_time VALUES (3, '3:50:20.56200'), (5, '3:50:20.56201'), (3, null);");

        $expectedResult = [[5, new Time(3, 50, 20, microseconds: 56201)]];

        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_time WHERE t = ?');
        $preparedStatement->bindParam(1, new Time(3, 50, 20, microseconds: 56201));
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
    }

    public function testPreparedStatementTimestamp(): void
    {
        $this->db->query('CREATE TABLE test_timestamp (i INTEGER, t TIMESTAMP);');
        $this->db->query("INSERT INTO test_timestamp VALUES (3, '2024-12-31 3:50:20.56200'), (5, '2024-12-31 3:50:20.56201'), (3, null);");

        $expectedResult = [[
            5,
            new Timestamp(
                new Date(2024, 12, 31),
                new Time(3, 50, 20, microseconds: 56201),
            ),
        ]];

        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_timestamp WHERE t = ?');
        $preparedStatement->bindParam(
            1,
            new Timestamp(
                new Date(2024, 12, 31),
                new Time(3, 50, 20, microseconds: 56201),
            )
        );
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
    }

    public function testPreparedStatementTimestampNs(): void
    {
        $this->db->query('CREATE TABLE test_timestamp (i INTEGER, t TIMESTAMP_NS);');
        $this->db->query("INSERT INTO test_timestamp VALUES (3, '2024-12-31 3:50:20.562010001'), (5, '2024-12-31 3:50:20.56201'), (3, null);");

        $expectedResult = [[
            3,
            new Timestamp(
                new Date(2024, 12, 31),
                new Time(3, 50, 20, nanoseconds: 562010001),
            ),
        ]];

        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_timestamp WHERE t = ?');
        $preparedStatement->bindParam(
            1,
            new Timestamp(
                new Date(2024, 12, 31),
                new Time(3, 50, 20, nanoseconds: 562010001),
            ),
            Type::DUCKDB_TYPE_TIMESTAMP_NS,
        );
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
    }

    public function testPreparedStatementBlob(): void
    {
        $this->db->query('CREATE TABLE test_blob (i INTEGER, b BLOB);');
        $expectedValues = [3, '123\xAA\xAB\xAC'];

        $this->db->query("INSERT INTO test_blob VALUES (3, '123\\xAA\\xAB\\xAC'), (5, '123\\xAA\\xAB\\xAC\\xAD'), (3, null);");
        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_blob WHERE b = ?');
        $preparedStatement->bindParam(1, Blob::fromHexEncodedString('123\\xAA\\xAB\\xAC'));
        $result = $preparedStatement->execute();

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }

    public function testDecimal(): void
    {
        $this->db->query('CREATE TABLE test_decimal (i INTEGER, d DECIMAL);');
        $expectedValues = [3, 12.3];

        $this->db->query('INSERT INTO test_decimal VALUES (3, 12.3), (5, 12.4), (3, null);');
        $preparedStatement = $this->db->preparedStatement('SELECT * FROM test_decimal WHERE d = ?');
        $preparedStatement->bindParam(1, 12.3, Type::DUCKDB_TYPE_DECIMAL);
        $result = $preparedStatement->execute();

        $row = $result->rows()->current();
        $this->assertEquals($expectedValues, $row);
    }
}
