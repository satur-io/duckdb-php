<?php

declare(strict_types=1);

namespace Integration;

use PHPUnit\Framework\TestCase;
use Saturio\DuckDB\Appender\Appender;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Exception\AppenderEndRowException;
use Saturio\DuckDB\Exception\AppenderFlushException;
use Saturio\DuckDB\Exception\AppendValueException;
use Saturio\DuckDB\Exception\ErrorCreatingNewAppender;

class AppenderTest extends TestCase
{
    private DuckDB $db;
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'file.db';
        $this->db = DuckDB::create();
        $this->db->query('CREATE TABLE people (id INTEGER, name VARCHAR)');
        $this->db->query("ATTACH '{$this->dbFile}' AS file_db;");
        $this->db->query('CREATE SCHEMA file_db.other_schema;');
        $this->db->query('CREATE TABLE file_db.other_schema.other_people (id INTEGER, name VARCHAR)');
    }

    protected function tearDown(): void
    {
        unset($this->db);
        unlink($this->dbFile);
    }

    public function testCreateAppender()
    {
        $appender = $this->db->appender('people');
        $this->assertInstanceOf(Appender::class, $appender);
    }

    public function testCreateAppenderForSpecificSchemaAndCatalog()
    {
        $appender = $this->db->appender('other_people', 'other_schema', 'file_db');
        $this->assertInstanceOf(Appender::class, $appender);
    }

    public function testErrorCreatingAppenderForNonExistingTable()
    {
        $this->expectException(ErrorCreatingNewAppender::class);
        $this->db->appender('this-table-does-not-exist');
    }

    public function testErrorAppendingWrongType()
    {
        $this->expectException(AppendValueException::class);
        $appender = $this->db->appender('people');
        $appender->append('this-is-a-non-integer-value');
    }

    public function testErrorEndingRow()
    {
        $this->expectException(AppenderEndRowException::class);
        $appender = $this->db->appender('people');
        $appender->append(1);
        $appender->endRow();
    }

    public function testFlushError()
    {
        $this->expectException(AppenderFlushException::class);
        $appender = $this->db->appender('people');
        $appender->append(1);
        $appender->flush();
    }

    public function testAppendRow()
    {
        $appender = $this->db->appender('people');
        $appender->append(1);
        $appender->append('this-is-a-varchar-value');
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM people');
        $this->assertEquals([1, 'this-is-a-varchar-value'], $total->rows()->current());
    }

    public function testAppendMultipleRows()
    {
        $appender = $this->db->appender('people');

        for ($i = 0; $i < 100; ++$i) {
            $appender->append(rand(1, 100000));
            $appender->append('this-is-a-varchar-value'.rand(1, 100));
            $appender->endRow();
        }

        $appender->flush();
        $total = $this->db->query('SELECT count(id) FROM people');
        $this->assertEquals([100], $total->rows()->current());
    }

    public function testAppendNull()
    {
        $appender = $this->db->appender('people');

        for ($i = 0; $i < 10; ++$i) {
            $appender->append(rand(1, 100000));
            $appender->append('this-is-a-varchar-value'.rand(1, 100));
            $appender->endRow();
        }

        for ($i = 0; $i < 10; ++$i) {
            $appender->append(rand(1, 100000));
            $appender->append(null);
            $appender->endRow();
        }

        $appender->flush();
        $total = $this->db->query('SELECT count(id) FROM people');
        $this->assertEquals([20], $total->rows()->current());

        $nullValues = $this->db->query('SELECT count(id) FROM people WHERE name is null');
        $this->assertEquals([10], $nullValues->rows()->current());
    }
}
