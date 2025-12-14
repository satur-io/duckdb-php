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

class FastAppenderTest extends TestCase
{
    private DuckDB $db;
    private string $dbFile;

    protected function setUp(): void
    {
        $this->dbFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'file.db';
        $this->db = DuckDB::create();
        $this->db->query('CREATE TABLE people (id INTEGER NOT NULL DEFAULT 1, name VARCHAR)');
        $this->db->query("ATTACH '{$this->dbFile}' AS file_db;");
        $this->db->query('CREATE SCHEMA file_db.other_schema;');
        $this->db->query('CREATE TABLE file_db.other_schema.other_people (id INTEGER NOT NULL DEFAULT 1, name VARCHAR)');
        $this->db->query('CREATE TABLE bool_table (value BOOLEAN)');
        $this->db->query('CREATE TABLE float_table (value FLOAT)');
    }

    protected function tearDown(): void
    {
        unset($this->db);
        unlink($this->dbFile);
    }

    public function testCreateAppender(): void
    {
        $appender = $this->db->appender('people');
        $this->assertInstanceOf(Appender::class, $appender);
    }

    public function testCreateAppenderForSpecificSchemaAndCatalog(): void
    {
        $appender = $this->db->appender('other_people', 'other_schema', 'file_db');
        $this->assertInstanceOf(Appender::class, $appender);
    }

    public function testErrorCreatingAppenderForNonExistingTable(): void
    {
        $this->expectException(ErrorCreatingNewAppender::class);
        $this->db->appender('this-table-does-not-exist');
    }

    public function testErrorAppendingWrongType(): void
    {
        $this->expectException(AppendValueException::class);
        $appender = $this->db->appender('people');
        $appender->fastAppend('this-is-a-non-integer-value');
    }

    public function testErrorAppendingUnexpectedType(): void
    {
        $this->expectException(AppendValueException::class);
        $this->expectExceptionMessage("Couldn't append this-is-a-non-integer-value. Error: Could not convert string 'this-is-a-non-integer-value' to INT32");
        $appender = $this->db->appender('people');
        $appender->fastAppend('this-is-a-non-integer-value');
    }

    public function testErrorEndingRow(): void
    {
        $this->expectException(AppenderEndRowException::class);
        $appender = $this->db->appender('people');
        $appender->fastAppend(1);
        $appender->endRow();
    }

    public function testFlushError(): void
    {
        $this->expectException(AppenderFlushException::class);
        $appender = $this->db->appender('people');
        $appender->fastAppend(1);
        $appender->flush();
    }

    public function testAppend(): void
    {
        $appender = $this->db->appender('people');
        $appender->fastAppend(1);
        $appender->fastAppend('this-is-a-varchar-value');
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM people');
        $this->assertEquals([1, 'this-is-a-varchar-value'], $total->rows()->current());
    }

    public function testAppendMultipleRows(): void
    {
        $appender = $this->db->appender('people');

        for ($i = 0; $i < 100; ++$i) {
            $appender->fastAppend(rand(1, 100000));
            $appender->fastAppend('this-is-a-varchar-value'.rand(1, 100));
            $appender->endRow();
        }

        $appender->flush();
        $total = $this->db->query('SELECT count(id) FROM people');
        $this->assertEquals([100], $total->rows()->current());
    }

    public function testAppendNull(): void
    {
        $appender = $this->db->appender('people');

        for ($i = 0; $i < 10; ++$i) {
            $appender->fastAppend(rand(1, 100000));
            $appender->fastAppend('this-is-a-varchar-value'.rand(1, 100));
            $appender->endRow();
        }

        for ($i = 0; $i < 5; ++$i) {
            $appender->fastAppend(rand(1, 100000));
            $appender->fastAppend(null);
            $appender->endRow();
        }

        for ($i = 0; $i < 5; ++$i) {
            $appender->fastAppend(rand(1, 100000));
            $appender->appendNull();
            $appender->endRow();
        }

        $appender->flush();
        $total = $this->db->query('SELECT count(id) FROM people');
        $this->assertEquals([20], $total->rows()->current());

        $nullValues = $this->db->query('SELECT count(id) FROM people WHERE name is null');
        $this->assertEquals([10], $nullValues->rows()->current());
    }

    public function testAppendNotNullError(): void
    {
        $this->expectException(AppenderEndRowException::class);
        $appender = $this->db->appender('people');
        $appender->fastAppend(null);
        $appender->endRow();
        $appender->flush();
    }

    public function testAppendDefault(): void
    {
        $appender = $this->db->appender('people');
        $appender->appendDefault();
        $appender->fastAppend('this-is-a-varchar-value');
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT id FROM people');
        $this->assertEquals([1], $total->rows()->current());
    }

    public function testAppendBool(): void
    {
        $appender = $this->db->appender('bool_table');
        $appender->fastAppend(true);
        $appender->endRow();
        $appender->fastAppend(true);
        $appender->endRow();
        $appender->fastAppend(false);
        $appender->endRow();
        $appender->appendBool(true);
        $appender->endRow();
        $appender->appendBool(false);
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM bool_table');
        $this->assertEquals([[true], [true], [false], [true], [false]], iterator_to_array($total->rows()));
    }

    public function testAppendVarchar(): void
    {
        $appender = $this->db->appender('people');
        $appender->fastAppend(1);
        $appender->appendVarchar('this-is-a-varchar-value');
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM people');
        $this->assertEquals([1, 'this-is-a-varchar-value'], $total->rows()->current());
    }

    public function testAppendInt(): void
    {
        $appender = $this->db->appender('people');
        $appender->appendInt(1);
        $appender->fastAppend('this-is-a-varchar-value');
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM people');
        $this->assertEquals([1, 'this-is-a-varchar-value'], $total->rows()->current());
    }

    public function testAppendFloat(): void
    {
        $appender = $this->db->appender('float_table');
        $appender->fastAppend(1.4);
        $appender->endRow();
        $appender->fastAppend(0.5);
        $appender->endRow();
        $appender->fastAppend(3);
        $appender->endRow();
        $appender->appendFloat(1.2);
        $appender->endRow();
        $appender->appendFloat(1.1);
        $appender->endRow();
        $appender->flush();
        $total = $this->db->query('SELECT * FROM float_table');
        $this->assertEqualsWithDelta([[1.4], [0.5], [3], [1.2], [1.1]], iterator_to_array($total->rows()), 0.01);
    }

    public function testAppendRow(): void
    {
        $appender = $this->db->appender('people');
        $appender->appendRow(1, 'this-is-a-varchar-value');
        $appender->flush();
        $total = $this->db->query('SELECT * FROM people');
        $this->assertEquals([1, 'this-is-a-varchar-value'], $total->rows()->current());
    }
}
