<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DB\InstanceCache;
use Saturio\DuckDB\DuckDB;

class InstanceCacheTest extends TestCase
{
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'file.db';
    }

    protected function tearDown(): void
    {
        unset($this->db);
        unlink($this->dbFile);
    }

    public function testInstanceCache(): void
    {
        $duckDB_1 = DuckDB::create($this->dbFile, instanceCache: true);
        $this->assertInstanceOf(InstanceCache::class, $duckDB_1->getInstanceCache());

        $duckDB_2 = DuckDB::create($this->dbFile, instanceCache: $duckDB_1->getInstanceCache());

        $duckDB_1->query('CREATE TABLE IF NOT EXISTS test (i INT);');
        $duckDB_1->query('INSERT INTO test VALUES (1), (2), (3);');
        $result = iterator_to_array($duckDB_2->query('SELECT * FROM test;')->rows());

        $this->assertEquals([[1], [2], [3]], $result);
    }
}
