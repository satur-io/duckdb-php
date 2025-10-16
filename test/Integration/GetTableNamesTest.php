<?php declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\FFI\DuckDB as FFIDuckDB;
use Saturio\DuckDB\DuckDB;

class GetTableNamesTest extends TestCase
{
    private DuckDB $db;

    protected function setUp(): void
    {
        $this->db = DuckDB::create();
    }

    public function testGetTableNames()
    {
        $query = 'SELECT * FROM my_table JOIN f ON f.id = my_table.id';
        $tableNames = $this->db->getTableNames($query);
        \sort($tableNames);
        $this->assertSame(['f', 'my_table'], $tableNames);
    }
}
