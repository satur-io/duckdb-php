<?php

declare(strict_types=1);

namespace Benchmark;

use PhpBench\Attributes as Bench;
use Saturio\DuckDB\DuckDB;
use Saturio\DuckDB\Type\Type;

class AppenderBench
{
    private const QUANTITY = 1000000;
    private const BATCH = 1000;
    private const VARCHAR_LENGTH = 100;

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharDefaultMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append(base64_encode(random_bytes(self::VARCHAR_LENGTH)), Type::DUCKDB_TYPE_VARCHAR);
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharVarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharVarcharLengthMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar(base64_encode(random_bytes(self::VARCHAR_LENGTH)), self::VARCHAR_LENGTH);
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'varchar'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendVarcharFastMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_varchar (value VARCHAR);');

        $appender = $duckDB->appender('test_varchar');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->fastAppend(base64_encode(random_bytes(self::VARCHAR_LENGTH)));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64DefaultMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append(rand(0, PHP_INT_MAX), Type::DUCKDB_TYPE_BIGINT);
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64IntMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendInt(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64VarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar((string) rand(0, PHP_INT_MAX));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int64'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt64FastMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int64 (value INT8);');

        $appender = $duckDB->appender('test_int64');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->fastAppend(rand(0, PHP_INT_MAX));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32IntMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT4);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendInt(rand(0, rand(0, pow(2, 31) - 1)));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32VarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT4);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar((string) rand(0, pow(2, 31) - 1));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'int32'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendInt32FastMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_int32 (value INT4);');

        $appender = $duckDB->appender('test_int32');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->fastAppend(rand(0, pow(2, 31) - 1));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'bool'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendBoolDefaultMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_bool (value BOOLEAN);');

        $appender = $duckDB->appender('test_bool');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append((bool) rand(0, 1));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'bool'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendBoolDefaultMethodTyped(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_bool (value BOOLEAN);');

        $appender = $duckDB->appender('test_bool');

        foreach (range(0, self::QUANTITY) as $i) {
            @$appender->append((bool) rand(0, 1), Type::DUCKDB_TYPE_BOOLEAN);
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'bool'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendBoolBoolMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_bool (value BOOLEAN);');

        $appender = $duckDB->appender('test_bool');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendBool((bool) rand(0, 1));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'bool'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendBoolVarcharMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_bool (value BOOLEAN);');

        $appender = $duckDB->appender('test_bool');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->appendVarchar(rand(0, 1) ? 'true' : 'false');
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }

    #[Bench\Groups(['appender', 'bool'])]
    #[Bench\Revs(5)]
    #[Bench\Iterations(2)]
    public function benchAppendBoolFastMethod(): void
    {
        $duckDB = DuckDB::create();
        $duckDB->query('CREATE TABLE IF NOT EXISTS test_bool (value BOOLEAN);');

        $appender = $duckDB->appender('test_bool');

        foreach (range(0, self::QUANTITY) as $i) {
            $appender->fastAppend((bool) rand(0, 1));
            $appender->endRow();

            if (0 === $i % self::BATCH) {
                $appender->flush();
            }
        }

        $appender->flush();
    }
}
