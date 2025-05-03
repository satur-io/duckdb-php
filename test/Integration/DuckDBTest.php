<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\DB\Configuration;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Exception\ConnectionException;

class DuckDBTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        if (file_exists('./test,db')) {
            unlink('./test.db');
        }
        parent::tearDownAfterClass();
    }

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

    public function testInvalidPath(): void
    {
        $db = DuckDB::create('./test.db');
        $this->assertInstanceOf(DuckDB::class, $db);
    }

    public function testInvalidDatabase(): void
    {
        $this->expectException(ConnectionException::class);
        DuckDB::create('/invalid/path/test.db');
    }

    public function testWithConfiguration(): void
    {
        $config = new Configuration();
        $config->set('access_mode', 'READ_WRITE');
        $config->set('threads', '8');

        $duckdb = DuckDB::create(config: $config);

        $threadsConfigResult = $duckdb->query("SELECT value AS threads FROM duckdb_settings() WHERE name = 'threads';");
        $this->assertEquals(
            '8',
            $threadsConfigResult->rows()->current()[0],
        );

        $accessModeConfigResult = $duckdb->query("SELECT value AS access_mode FROM duckdb_settings() WHERE name = 'access_mode';");
        $this->assertEquals(
            'read_write',
            $accessModeConfigResult->rows()->current()[0],
        );
    }

    public function testWithWrongConfiguration(): void
    {
        $this->expectException(ConnectionException::class);
        $this->expectExceptionMessageMatches('/(not recognized).+(this_config_parameter)/i');
        $config = new Configuration();
        $config->set('this_config_parameter', 'does_not_exist');

        DuckDB::create(config: $config);
    }
}
