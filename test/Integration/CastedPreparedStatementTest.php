<?php

declare(strict_types=1);

namespace Integration;

use Integration\Helper\IntegrationTestTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Type\Date;
use Saturio\DuckDB\Type\Interval;
use Saturio\DuckDB\Type\Time;
use Saturio\DuckDB\Type\Timestamp;
use Saturio\DuckDB\Type\Type;

class CastedPreparedStatementTest extends TestCase
{
    use IntegrationTestTrait;
    private DuckDB $db;

    protected function setUp(): void
    {
        $this->db = DuckDB::create();
    }

    #[DataProvider('numericsProvider')]
    #[DataProvider('timeProvider')]
    #[DataProvider('varcharProvider')]
    public function testCastedPreparedStatement(Type $type, string $sqlType, mixed $searchValue, array $expectedResult, $values): void
    {
        $this->testType($type, $sqlType, $searchValue, $expectedResult, ...$values);
    }

    private function testType(Type $type, string $sqlType, mixed $searchValue, array $expectedResult, ...$values): void
    {
        $tableName = str_shuffle('abcdefghijklmnop');
        $this->createTableAndInsert($tableName, $sqlType, ...$values);
        $preparedStatement = $this->db->preparedStatement("SELECT x FROM '{$tableName}' WHERE x = ?;");
        $preparedStatement->bindParam(1, $searchValue, $type);
        $result = $preparedStatement->execute();

        $arrayResult = iterator_to_array($result->rows());

        $this->assertEquals($expectedResult, $arrayResult);
        $this->db->query("DROP TABLE {$tableName};");
    }

    private function createTableAndInsert(string $tableName, string $type, ...$values): void
    {
        $this->db->query("CREATE TABLE '{$tableName}' (x {$type});");
        $values = implode(', ', array_map(fn (mixed $v) => is_string($v) || is_object($v) ? "('$v')" : (is_null($v) ? '(null)' : "($v)"), $values));
        $this->db->query("INSERT INTO '{$tableName}' VALUES $values;");
    }

    public static function timeProvider(): array
    {
        $timestampSearch = new Timestamp(new Date(1521, 4, 23), new Time(12, 3, 2));
        $timestampResult = [[clone $timestampSearch], [clone $timestampSearch]];
        $timestampInsert = [clone $timestampSearch, null, clone $timestampSearch, new Timestamp(new Date(100, 1, 2), new Time(12, 3, 2))];

        $dateSearch = new Date(1521, 4, 23);
        $dateResult = [[clone $dateSearch], [clone $dateSearch]];
        $dateInsert = [clone $dateSearch, null, clone $dateSearch, new Date(100, 1, 2)];

        $timeSearch = new Time(12, 0, 23);
        $timeResult = [[clone $timeSearch], [clone $timeSearch]];
        $timeInsert = [clone $timeSearch, null, clone $timeSearch, new Time(10, 1, 2)];

        $intervalSearch = new Interval(10, 1);
        $intervalResult = [[clone $intervalSearch], [clone $intervalSearch]];
        $intervalInsert = [clone $intervalSearch, null, clone $intervalSearch, new Interval(10, 1, 2)];

        return [
            [Type::DUCKDB_TYPE_TIMESTAMP, 'TIMESTAMP', $timestampSearch, $timestampResult, $timestampInsert],
            [Type::DUCKDB_TYPE_DATE, 'DATE', $dateSearch, $dateResult, $dateInsert],
            [Type::DUCKDB_TYPE_TIME, 'TIME', $timeSearch, $timeResult, $timeInsert],
            [Type::DUCKDB_TYPE_INTERVAL, 'INTERVAL', $intervalSearch, $intervalResult, $intervalInsert],
        ];
    }

    public static function numericsProvider(): array
    {
        return [
            [Type::DUCKDB_TYPE_TINYINT, 'TINYINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_SMALLINT, 'SMALLINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_INTEGER, 'INTEGER', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_BIGINT, 'BIGINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_UTINYINT, 'UTINYINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_USMALLINT, 'USMALLINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_UINTEGER, 'UINTEGER', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_UBIGINT, 'UBIGINT', 3, [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_UBIGINT, 'UBIGINT', '3', [[3], [3]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_FLOAT, 'FLOAT', 3.0, [[3.0], [3.0]], [3, 5, 6, 3, null]],
            [Type::DUCKDB_TYPE_DOUBLE, 'DOUBLE', 3.0, [[3.0], [3.0]], [3, 5, 6, 3, null]],
        ];
    }

    public static function varcharProvider(): array
    {
        return [
            [Type::DUCKDB_TYPE_VARCHAR, 'VARCHAR', 'quack', [['quack'], ['quack']], ['quack', 'quick', 'quick', 'quack', null]],
        ];
    }
}
