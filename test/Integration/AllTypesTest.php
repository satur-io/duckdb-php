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
        $exclude = [
            'varint',
            'bit',
        ];

        $excludeSql = implode(', ', array_map(fn ($v) => "'$v'", $exclude));

        $result = $this->db->query("SELECT * EXCLUDE($excludeSql) FROM test_all_types();");

        self::assertEquals(54 - count($exclude), $result->columnCount());
        iterator_to_array($result->rows(columnNameAsKey: true));
    }
}
