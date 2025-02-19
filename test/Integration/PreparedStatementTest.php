<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use SaturIo\DuckDB\DuckDB;
use SaturIo\DuckDB\Type\Date;
use SaturIo\DuckDB\Type\Time;
use SaturIo\DuckDB\Type\Timestamp;

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
}
