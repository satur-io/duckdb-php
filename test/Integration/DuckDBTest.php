<?php

declare(strict_types=1);

namespace Integration;

use Integration\Helper\IntegrationTestTrait;
use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Exception\ConnectionException;

class DuckDBTest extends TestCase
{
    use IntegrationTestTrait;

    public function testInMemory(): void
    {
        $db = DuckDB::create();
        $this->assertInstanceOf(DuckDB::class, $db);
    }

    public function testErrorOpen(): void
    {
        self::expectException(ConnectionException::class);
        $forbiddenFile = sys_get_temp_dir().'/no-permissions';
        touch($forbiddenFile);
        chmod($forbiddenFile, 0000);
        DuckDB::create($forbiddenFile);
    }

    public function testInValidPath(): void
    {
        $db = DuckDB::create('./test.db');
        $this->assertInstanceOf(DuckDB::class, $db);
    }

    public function testInValidDatabase(): void
    {
        $this->expectException(ConnectionException::class);
        DuckDB::create('/invalid/path/test.db');
    }
}
